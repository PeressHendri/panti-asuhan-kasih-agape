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
            'status' => 'required|in:check_in,check_out'
        ]);

        $fotoBase64 = $validated['foto_base64'];
        $status = $validated['status'];

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

        // Path script python untuk CNN
        $pythonScriptPath = base_path('recognition_engine/scripts/recognize_cnn.py');
        
        // Gunakan Python 3.10 yang memiliki numpy + cv2.face (opencv-contrib) yang kompatibel
        // python3.10 = Homebrew Python 3.10 yang sudah terinstall opencv-contrib-python
        $candidates = [
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

        $command = $pythonBin . " " . escapeshellarg($pythonScriptPath) . " " . escapeshellarg($fullPath) . " 2>&1";
        $output = shell_exec($command);

        $result = json_decode($output, true);

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

        $childId = $result['child_id'];
        $confidence = $result['confidence'];
        $nama = $result['nama'];

        // Simpan Log Absensi Wajah (id UUID di-generate otomatis oleh HasUuids)
        $log = FaceRecognitionLog::create([
            'child_id' => $childId,
            'confidence_score' => $confidence,
            'algoritma' => 'cnn',
            'status' => $status,
            'kamera_id' => 'website_webcam',
            'foto_capture_path' => $filename,
        ]);

        // Catat di tabel Attendance (Kehadiran Utama)
        $today = Carbon::today();
        $attendance = Attendance::firstOrNew([
            'child_id' => $childId,
            'date' => $today
        ]);

        if ($status === 'check_in') {
            if (!$attendance->check_in) {
                $attendance->check_in = now();
            }
            $attendance->status = 'hadir';
        } else if ($status === 'check_out') {
            $attendance->check_out = now();
            if (!$attendance->status) {
                $attendance->status = 'hadir';
            }
        }

        $attendance->kamera_id = 'website_webcam';
        $attendance->confidence_score = $confidence;
        $attendance->algoritma = 'cnn';
        $attendance->foto_capture_path = $filename;
        $attendance->save();

        // Kirim event notifikasi real-time jika ada WebSocket terpasang
        try {
            event(new \App\Events\CctvMotionDetected([
                'lokasi' => 'website_webcam',
                'status' => "Absen Wajah (Web): {$nama} ({$status})"
            ]));
        } catch (\Exception $e) {
            // Abaikan jika event broadcast tidak dikonfigurasi
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengenali {$nama}!",
            'data' => [
                'nama' => $nama,
                'confidence' => $confidence,
                'status' => $status === 'check_in' ? 'Check In (Masuk)' : 'Check Out (Keluar)',
                'waktu' => now()->format('H:i:s'),
                'foto' => asset('storage/' . $filename)
            ]
        ]);
    }
}

