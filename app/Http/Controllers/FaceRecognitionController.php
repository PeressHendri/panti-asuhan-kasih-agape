<?php

namespace App\Http\Controllers;

use App\Models\FaceRecognitionLog;
use App\Models\Attendance;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FaceRecognitionController extends Controller
{
    public function dashboard(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $query = FaceRecognitionLog::with('child')
            ->whereDate('waktu_deteksi', $date);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->latest('waktu_deteksi')->paginate(20);

        $stats = [
            'total' => FaceRecognitionLog::whereDate('waktu_deteksi', $date)->count(),
            'check_in' => FaceRecognitionLog::whereDate('waktu_deteksi', $date)
                ->where('status', 'check_in')->count(),
            'check_out' => FaceRecognitionLog::whereDate('waktu_deteksi', $date)
                ->where('status', 'check_out')->count(),
            'tidak_dikenal' => FaceRecognitionLog::whereDate('waktu_deteksi', $date)
                ->where('status', 'tidak_dikenal')->count(),
        ];

        return view('dashboard.face-log.index', compact('logs', 'stats', 'date'));
    }

    /**
     * Halaman absensi wajah via webcam browser.
     */
    public function webFaceRecognitionPage()
    {
        // Ambil kehadiran hari ini untuk ditampilkan di panel kanan
        $todayAttendances = Attendance::with('child')
            ->whereDate('date', Carbon::today())
            ->where(function($q) {
                $q->where('algoritma', 'lbph')
                  ->orWhere('kamera_id', 'website_webcam');
            })
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('dashboard.face-recognition-web', compact('todayAttendances'));
    }

    /**
     * API endpoint: ambil kehadiran hari ini via AJAX (untuk auto-refresh panel).
     */
    public function recentAttendance()
    {
        $list = Attendance::with('child')
            ->whereDate('date', Carbon::today())
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'nama'      => $a->child->nama ?? 'Tidak Diketahui',
                'check_in'  => $a->check_in  ? Carbon::parse($a->check_in)->format('H:i:s')  : null,
                'check_out' => $a->check_out ? Carbon::parse($a->check_out)->format('H:i:s') : null,
                'status'    => $a->status,
                'algoritma' => $a->algoritma ?? 'manual',
                'inisial'   => strtoupper(substr($a->child->nama ?? 'A', 0, 1)),
            ]);

        return response()->json(['data' => $list]);
    }

    /**
     * Menerima kiriman foto base64 dari webcam browser,
     * menjalankannya di script Python untuk mendeteksi wajah anak.
     */
    public function webScan(Request $request)
    {
        $validated = $request->validate([
            'foto_base64' => 'required|string',
        ]);

        $fotoBase64 = $validated['foto_base64'];
        // Status TIDAK diterima dari frontend — ditentukan otomatis oleh server

        // Decode base64 image
        $image_parts = explode(";base64,", $fotoBase64);
        if (count($image_parts) != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Format gambar tidak valid.'
            ], 400);
        }

        $image_base64 = base64_decode($image_parts[1]);
        
        // Buat folder public/captures jika belum ada
        $capturesDir = storage_path('app/public/captures');
        if (!file_exists($capturesDir)) {
            mkdir($capturesDir, 0775, true);
        }

        $filename = 'captures/web_' . Str::uuid() . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);
        
        file_put_contents($fullPath, $image_base64);

        // Path script python untuk Ensemble (LBPH Utama + VGG16 Fallback)
        $pythonScriptPath = base_path('recognition_engine/scripts/recognize_cnn.py');
        
        // Gunakan Python 3.10 yang memiliki numpy + cv2.face (opencv-contrib) yang kompatibel
        // python3.10 = Homebrew Python 3.10 yang sudah terinstall opencv-contrib-python
        $candidates = [
            base_path('venv/bin/python3'),
            base_path('venv/bin/python'),
            '/usr/local/bin/python3.10',
            '/opt/homebrew/bin/python3.10',
            '/usr/bin/python3.10',
            'python3.10',
            'python3',
        ];
        $pythonBin = 'python3'; // default fallback
        foreach ($candidates as $candidate) {
            if (str_contains($candidate, '/') && file_exists($candidate)) {
                $pythonBin = $candidate;
                break;
            } elseif (!str_contains($candidate, '/')) {
                // cek command yang ada di PATH
                $test = shell_exec("which {$candidate} 2>/dev/null");
                if (!empty(trim($test))) {
                    $pythonBin = trim($test);
                    break;
                }
            }
        }

        // Tambahkan PYTHONPATH secara dinamis agar pustaka terinstal (seperti cv2/deepface) terbaca di VPS CloudPanel & lokal
        $sitePackagesPaths = [];
        $homes = [
            '/home/pantiasuhankasihagape',
            getenv('HOME'),
            $_SERVER['HOME'] ?? '',
        ];
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $userInfo = posix_getpwuid(posix_geteuid());
            if ($userInfo && !empty($userInfo['dir'])) {
                $homes[] = $userInfo['dir'];
            }
        }
        
        foreach (array_unique(array_filter($homes)) as $home) {
            if (is_dir($home . '/.local/lib')) {
                $pythonDirs = glob($home . '/.local/lib/python3.*/site-packages');
                if (!empty($pythonDirs)) {
                    $sitePackagesPaths = array_merge($sitePackagesPaths, $pythonDirs);
                }
            }
        }
        
        $sitePackagesPaths = array_unique($sitePackagesPaths);
        
        if (!empty($sitePackagesPaths)) {
            $pythonPathEnv = "PYTHONPATH=" . implode(':', $sitePackagesPaths) . " ";
        } else {
            $pythonPathEnv = "";
        }
        
        $command = $pythonPathEnv . escapeshellarg($pythonBin) . " " . escapeshellarg($pythonScriptPath) . " " . escapeshellarg($fullPath) . " 2>&1";
        $output = shell_exec($command);

        // Cari string JSON di dalam output (menghilangkan log/warning TensorFlow dari STDERR)
        $jsonStart = strpos($output, '{');
        $jsonEnd = strrpos($output, '}');
        
        $result = null;
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
            $result = json_decode($jsonString, true);
        }

        if (!$result) {
            // Hapus file sementara jika gagal scan/error script
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pemindaian wajah di server.',
                'error_detail' => $output
            ], 500);
        }

        if (!$result['success']) {
            // Hapus file jika wajah tidak terdeteksi / tidak dikenal
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Wajah tidak dikenali.'
            ]);
        }

        $childId    = $result['child_id'];
        $confidence = $result['confidence'];
        $nama       = $result['nama'];

        // ── Auto-detect status berdasarkan data kehadiran hari ini ────────────
        $today      = Carbon::today();
        $attendance = Attendance::where('child_id', $childId)
                                ->whereDate('date', $today)
                                ->first();

        // Tentukan status secara otomatis
        if (!$attendance || !$attendance->check_in) {
            $status = 'check_in';   // Belum pernah masuk hari ini → Check In
        } elseif (!$attendance->check_out) {
            $status = 'check_out';  // Sudah Check In tapi belum Check Out → Check Out
        } else {
            // Sudah lengkap Check In + Check Out hari ini
            if (file_exists($fullPath)) unlink($fullPath);
            return response()->json([
                'success' => false,
                'message' => "{$nama} sudah menyelesaikan absensi hari ini (Check In: " .
                             Carbon::parse($attendance->check_in)->format('H:i') .
                             " | Check Out: " .
                             Carbon::parse($attendance->check_out)->format('H:i') . ")."
            ]);
        }

        // ── Buat atau update record attendance ────────────────────────────────
        if (!$attendance) {
            $attendance = new Attendance([
                'child_id' => $childId,
                'date'     => $today,
            ]);
        }

        if ($status === 'check_in') {
            $attendance->check_in = now();
            $attendance->status   = 'hadir';
        } else {
            $attendance->check_out = now();
            $attendance->status    = 'hadir';
        }

        $attendance->kamera_id          = 'website_webcam';
        $attendance->confidence_score   = $confidence;
        $attendance->algoritma          = 'lbph';
        $attendance->foto_capture_path  = $filename;
        $attendance->save();

        // ── Log ───────────────────────────────────────────────────────────────
        FaceRecognitionLog::create([
            'child_id'          => $childId,
            'confidence_score'  => $confidence,
            'algoritma'         => 'lbph',
            'status'            => $status,
            'kamera_id'         => 'website_webcam',
            'foto_capture_path' => $filename,
        ]);

        // Kirim event real-time (opsional)
        try {
            event(new \App\Events\CctvMotionDetected([
                'lokasi' => 'website_webcam',
                'status' => "Absen Wajah: {$nama} ({$status})"
            ]));
        } catch (\Exception $e) { /* abaikan jika broadcast tidak aktif */ }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengenali {$nama}!",
            'data'    => [
                'nama'       => $nama,
                'confidence' => $confidence,
                'status'     => $status === 'check_in' ? 'Check In (Masuk)' : 'Check Out (Keluar)',
                'waktu'      => now()->format('H:i:s'),
                'foto'       => asset('storage/' . $filename),
            ]
        ]);
    }
}
