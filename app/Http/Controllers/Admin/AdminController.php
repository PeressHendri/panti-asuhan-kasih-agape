<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Donation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengakses Dashboard Admin',
            'status' => 'Berhasil'
        ]);

        $stats = [
            'total_children' => Child::count(),
            'total_users' => User::count(),
            'total_admin' => User::where('role', 'admin')->count(),
            'total_pengasuh' => User::where('role', 'pengasuh')->count(),
            'total_sponsor' => User::where('role', 'sponsor')->count(),
        ];

        $activities = ActivityLog::with('user')->latest()->take(5)->get();

        // Data grafik 7 hari kehadiran
        $chartLabels = [];
        $chartHadir  = [];
        $chartAlpa   = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $chartLabels[] = $day->translatedFormat('D, d M');
            $chartHadir[]  = Attendance::whereDate('date', $day)->where('status', 'hadir')->count();
            $chartAlpa[]   = Attendance::whereDate('date', $day)->whereIn('status', ['alpa', 'alfa'])->count();
        }

        // Statistik kehadiran hari ini
        $todayHadir = Attendance::whereDate('date', Carbon::today())->where('status', 'hadir')->count();
        $todayAlpa  = Attendance::whereDate('date', Carbon::today())->whereNotIn('status', ['hadir'])->count();
        $totalAnak  = Child::count();

        // Unknown face total hari ini
        $unknownToday = \App\Models\FaceRecognitionLog::whereDate('waktu_deteksi', Carbon::today())
            ->where('status', 'tidak_dikenal')->count();

        // Raspberry Pi Status (Check last ping from any camera log)
        $maxPing = \App\Models\FaceRecognitionLog::max('waktu_deteksi');
        $isPiOnline = $maxPing && Carbon::parse($maxPing)->diffInMinutes(now()) <= 10;

        return view('admin.dashboard', compact(
            'stats', 'activities',
            'chartLabels', 'chartHadir', 'chartAlpa',
            'todayHadir', 'todayAlpa', 'totalAnak', 'unknownToday', 'isPiOnline'
        ));
    }

    public function profilePanti(Request $request)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Melihat Profil Panti',
            'status' => 'Berhasil'
        ]);

        $search = $request->input('search');
        $query = Child::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $children = $query->paginate(10);
        return view('admin.profile-panti', compact('children'));
    }

    public function create()
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Membuka Form Tambah Data Anak',
            'status' => 'Berhasil'
        ]);

        return view('crud.profile-panti-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'nim' => 'nullable|digits:16|numeric',
            'sekolah' => 'nullable|string|max:255',
            'panti_id' => 'nullable|exists:pantis,id',
            'photo' => 'nullable|image|max:10240', // 10MB
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('children-photos', 'public');
        }

        $child = Child::create($validated);
        Log::info('Data disimpan: ', $child->toArray());

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Menambahkan Data Anak: ' . $validated['nama'],
            'status' => 'Berhasil'
        ]);

        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil ditambahkan.');
    }

    public function edit($id)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Membuka Form Edit Data Anak',
            'status' => 'Berhasil'
        ]);

        $child = Child::findOrFail($id);
        return view('crud.profile-panti-edit', compact('child'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'nim' => 'nullable|digits:16|numeric',
            'sekolah' => 'nullable|string|max:255',
            'panti_id' => 'nullable|exists:pantis,id',
            'photo' => 'nullable|image|max:10240', // 10MB
        ]);

        $child = Child::findOrFail($id);

        $data = array_filter($validated, function ($v) {
            return $v !== null && $v !== ''; });

        if ($request->hasFile('photo')) {
            if ($child->photo) {
                Storage::disk('public')->delete($child->photo);
            }
            $data['photo'] = $request->file('photo')->store('children-photos', 'public');
        }

        $child->update($data);

        Log::info('Data diperbarui: ', $child->toArray());

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengedit Data Anak: ' . $child->nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $child = Child::findOrFail($id);
        $nama = $child->nama; // Simpan nama sebelum dihapus
        if ($child->photo) {
            Storage::disk('public')->delete($child->photo); // Hapus foto jika ada
        }
        $child->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Menghapus Data Anak: ' . $nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->route('admin.profile.panti')->with('success', 'Data anak berhasil dihapus.');
    }

    public function manageUsers()
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengakses Manajemen Pengguna',
            'status' => 'Berhasil'
        ]);

        $users = User::whereIn('role', ['admin', 'pengasuh', 'sponsor'])->paginate(10);
        return view('admin.manage-users', compact('users'));
    }

    public function cctv()
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengakses CCTV',
            'status' => 'Berhasil'
        ]);
        return view('cctv.cctv');
    }

    public function attendance(Request $request)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Melihat Dashboard Kehadiran Terpadu',
            'status' => 'Berhasil'
        ]);

        $date = $request->input('date', Carbon::today()->toDateString());
        $range = $request->input('range', 'today'); // 'today', 'week', 'month', 'custom'

        // 1. Fetch Official Attendance
        $attendancesQuery = Attendance::with('child')->orderBy('date', 'desc')->orderBy('check_in', 'desc');

        // Apply Range Filter — define $startDate at top level to avoid undefined variable error
        $startDate = null;
        if ($range === 'week') {
            $startDate = Carbon::today()->subDays(7);
            $attendancesQuery->whereDate('date', '>=', $startDate);
        } elseif ($range === 'month') {
            $startDate = Carbon::today()->subDays(30);
            $attendancesQuery->whereDate('date', '>=', $startDate);
        } elseif ($range === 'custom') {
            $attendancesQuery->whereRaw('DATE(date) = ?', [$date]);
        } else {
            // today (default)
            $attendancesQuery->whereRaw('DATE(date) = ?', [Carbon::today()->toDateString()]);
        }

        if ($request->filled('status')) {
            $attendancesQuery->where('status', $request->status);
        }
        $attendances = $attendancesQuery->get();

        // 2. Fetch Face Recognition Logs (History)
        $faceLogsQuery = \App\Models\FaceRecognitionLog::with('child');

        if ($range === 'week') {
            $faceLogsQuery->whereDate('waktu_deteksi', '>=', $startDate);
        } elseif ($range === 'month') {
            $faceLogsQuery->whereDate('waktu_deteksi', '>=', $startDate);
        } elseif ($range === 'custom') {
            $faceLogsQuery->whereDate('waktu_deteksi', $date);
        } else {
            $faceLogsQuery->whereDate('waktu_deteksi', Carbon::today()->toDateString());
        }
        
        if ($request->filled('status_ai')) {
            $faceLogsQuery->where('status', $request->status_ai);
        }
        $faceLogs = $faceLogsQuery->latest('waktu_deteksi')->paginate(50, ['*'], 'face_page');

        // Prepare raw query components for stats
        $baseFaceLogQuery = \App\Models\FaceRecognitionLog::query();
        if ($range === 'week' || $range === 'month') {
            $baseFaceLogQuery->whereDate('waktu_deteksi', '>=', $startDate);
        } elseif ($range === 'custom') {
            $baseFaceLogQuery->whereDate('waktu_deteksi', $date);
        } else {
            $baseFaceLogQuery->whereDate('waktu_deteksi', Carbon::today()->toDateString());
        }

        // 3. Simple Stats
        $stats = [
            'total_attendance' => $attendances->count(),
            'total_ai_logs' => (clone $baseFaceLogQuery)->count(),
            'unknown_faces' => (clone $baseFaceLogQuery)->where('status', 'tidak_dikenal')->count(),
            'check_in_ai' => (clone $baseFaceLogQuery)->where('status', 'check_in')->count(),
        ];

        // 4. Raspberry Pi Status (Check last ping from any camera log)
        $maxPing = \App\Models\FaceRecognitionLog::max('waktu_deteksi');
        $isPiOnline = $maxPing && Carbon::parse($maxPing)->diffInMinutes(now()) <= 10; // 10 mins threshold

        $children = Child::orderBy('nama')->get();

        return view('admin.attendance', compact('attendances', 'faceLogs', 'stats', 'date', 'isPiOnline', 'children'));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:children,id', // Pastikan nama tabel benar
            'date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa'
        ]);

        $existing = Attendance::where('child_id', $request->child_id)
            ->whereRaw('DATE(date) = ?', [$request->date])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Anak ini sudah melakukan check-in untuk tanggal tersebut');
        }

        $attendance = Attendance::create([
            'child_id' => $request->child_id,
            'date' => $request->date,
            'check_in' => Carbon::now(),
            'status' => $request->status,
            'note' => $request->note
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Check-in anak: ' . $attendance->child->nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->back()
            ->with('success', 'Check-in berhasil dicatat');
    }

    public function checkOut($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'check_out' => now()
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Check-out anak: ' . ($attendance->child->nama ?? 'Unknown'),
            'status' => 'Berhasil'
        ]);

        return redirect()->back()
            ->with('success', 'Check-out berhasil dicatat');
    }

    public function manualAttendance(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:children,id', // Pastikan nama tabel benar
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'note' => 'nullable|string'
        ]);

        $checkIn = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->check_in);
        $checkOut = $request->check_out
            ? Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->check_out)
            : null;

        // Cek apakah sudah ada data untuk anak dan tanggal yang sama
        $existing = Attendance::where('child_id', $request->child_id)
            ->whereRaw('DATE(date) = ?', [$request->date])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Data kehadiran untuk anak ini pada tanggal tersebut sudah ada');
        }

        $attendance = Attendance::create([
            'child_id' => $request->child_id,
            'date' => $request->date,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $request->status,
            'note' => $request->note
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Input manual kehadiran untuk: ' . $attendance->child->nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->back()
            ->with('success', 'Data kehadiran manual berhasil disimpan');
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendances,id',
            'status' => 'required|in:hadir,sakit,izin',
            'note' => 'nullable|string'
        ]);

        $attendance = Attendance::findOrFail($request->id);
        $attendance->update([
            'status' => $request->status,
            'note' => $request->note
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengupdate kehadiran untuk: ' . $attendance->child->nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->back()
            ->with('success', 'Data kehadiran berhasil diperbarui');
    }

    public function deleteAttendance($id)
    {
        $attendance = Attendance::findOrFail($id);
        $nama = $attendance->child->nama ?? 'Unknown';
        
        $attendance->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Menghapus data kehadiran: ' . $nama,
            'status' => 'Berhasil'
        ]);

        return redirect()->back()->with('success', 'Data kehadiran berhasil dihapus.');
    }

    // =============================================
    // EXPORT LAPORAN KEHADIRAN (CSV)
    // =============================================
    public function exportAttendance(Request $request)
    {
        $range  = $request->input('range', 'month');
        $date   = $request->input('date', Carbon::today()->toDateString());

        $query = Attendance::with('child')->orderBy('date', 'desc');
        if ($range === 'week')  { $query->whereDate('date', '>=', Carbon::today()->subDays(7)); }
        elseif ($range === 'month') { $query->whereDate('date', '>=', Carbon::today()->subDays(30)); }
        elseif ($range === 'custom') { $query->whereRaw('DATE(date) = ?', [$date]); }
        else { $query->whereDate('date', Carbon::today()); }

        $attendances = $query->get();

        $filename = 'laporan_kehadiran_' . $range . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            // BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No', 'Nama Anak', 'Tanggal', 'Check In', 'Check Out', 'Status', 'Keterangan', 'Algoritma']);
            foreach ($attendances as $i => $a) {
                fputcsv($file, [
                    $i + 1,
                    $a->child->nama ?? '-',
                    Carbon::parse($a->date)->format('d/m/Y'),
                    $a->check_in  ? Carbon::parse($a->check_in)->format('H:i:s')  : '-',
                    $a->check_out ? Carbon::parse($a->check_out)->format('H:i:s') : '-',
                    strtoupper($a->status),
                    $a->note ?? '-',
                    strtoupper($a->algoritma ?? 'manual'),
                ]);
            }
            fclose($file);
        };

        ActivityLog::create([
            'user_id'  => Auth::id(),
            'activity' => 'Export Laporan Kehadiran (' . $range . ')',
            'status'   => 'Berhasil'
        ]);

        return response()->stream($callback, 200, $headers);
    }

    public function editProfile()
    {
        $user = auth()->user();
        $confidenceThreshold = \Illuminate\Support\Facades\Cache::get('confidence_threshold', 75);
        $dashboard = route('admin.dashboard');
        return view('admin.edit-profile', compact('user', 'confidenceThreshold', 'dashboard'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email,' . $user->id,
            'password'             => 'nullable|confirmed|min:6',
            'photo'                => 'nullable|image|max:2048',
            'confidence_threshold' => 'nullable|integer|min:40|max:99',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('profile-photos', 'public');
        }
        $user->save();

        if ($request->has('enable_manual_attendance')) {
            \Illuminate\Support\Facades\Cache::forever('enable_manual_attendance', true);
        } else {
            \Illuminate\Support\Facades\Cache::forever('enable_manual_attendance', false);
        }

        // Simpan Confidence Threshold
        $threshold = $request->input('confidence_threshold', 75);
        \Illuminate\Support\Facades\Cache::forever('confidence_threshold', (int) $threshold);

        ActivityLog::create([
            'user_id'  => $user->id,
            'activity' => 'Mengedit Profil Admin',
            'status'   => 'Berhasil'
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Profil berhasil diperbarui!');
    }

    // Endpoint API-like untuk ambil threshold (dipanggil Python)
    public function getSettings()
    {
        return response()->json([
            'confidence_threshold'     => \Illuminate\Support\Facades\Cache::get('confidence_threshold', 75),
            'enable_manual_attendance' => \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false),
        ]);
    }

    // =============================================
    // MANAJEMEN DONASI (Verifikasi)
    // =============================================
    public function donasiIndex(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = Donation::with(['child', 'user'])->orderBy('created_at', 'desc');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        $donations = $query->paginate(15);

        $stats = [
            'pending'   => Donation::where('status', 'pending')->count(),
            'konfirmasi'=> Donation::where('status', 'konfirmasi')->count(),
            'ditolak'   => Donation::where('status', 'ditolak')->count(),
            'total'     => Donation::count(),
        ];

        return view('admin.donasi', compact('donations', 'stats', 'status'));
    }

    public function donasiVerify(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:konfirmasi,ditolak',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $donation = Donation::findOrFail($id);
        $donation->status        = $request->status;
        $donation->catatan_admin = $request->catatan_admin;
        $donation->save();

        ActivityLog::create([
            'user_id'  => Auth::id(),
            'activity' => 'Verifikasi donasi dari ' . $donation->nama_donatur . ' → ' . strtoupper($request->status),
            'status'   => 'Berhasil'
        ]);

        $msg = $request->status === 'konfirmasi' ? 'Donasi berhasil dikonfirmasi! ✅' : 'Donasi telah ditolak.';
        return redirect()->back()->with('success', $msg);
    }

    // Sinkronisasi label_map.json → child_id database
    public function syncLabelMap()
    {
        $labelMapPath = base_path('recognition_engine/models/lbph/label_map.json');

        if (!file_exists($labelMapPath)) {
            return response()->json(['error' => 'label_map.json tidak ditemukan!'], 404);
        }

        $rawMap = json_decode(file_get_contents($labelMapPath), true);
        $synced = [];
        $notFound = [];

        foreach ($rawMap as $lbphIndex => $labelName) {
            // Format nama di label_map: Nama_Lengkap_NIM  → pisahkan bagian terakhir (NIM/nomor)
            // Coba cocokkan berdasarkan sebagian nama
            $parts = explode('_', $labelName);
            // Nama biasanya 3-4 kata, nomor di akhir
            $nameParts = array_filter($parts, fn($p) => !is_numeric($p));
            $searchName = implode(' ', array_slice(array_values($nameParts), 0, 3));

            $child = Child::where('nama', 'like', '%' . $searchName . '%')->first();

            if ($child) {
                $synced[$lbphIndex] = [
                    'label_name' => $labelName,
                    'child_id'   => $child->id,
                    'nama_db'    => $child->nama,
                ];
            } else {
                $notFound[$lbphIndex] = $labelName;
            }
        }

        return response()->json([
            'message'   => 'Sinkronisasi selesai.',
            'synced'    => $synced,
            'not_found' => $notFound,
            'total'     => count($rawMap),
            'matched'   => count($synced),
        ]);
    }

    // =============================================
    // CI/CD DEPLOYMENT (PULL DARI GITHUB)
    // =============================================
    public function deploy(Request $request)
    {
        try {
            $basePath = base_path();
            $commands = [
                "git pull origin main 2>&1",
                "composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1",
                "php artisan optimize:clear 2>&1",
                "php artisan migrate --force 2>&1"
            ];
            
            $output = [];
            foreach ($commands as $command) {
                $output[] = shell_exec("cd {$basePath} && {$command}");
            }
            
            \Illuminate\Support\Facades\Log::info("Deployment dijalankan oleh User ID: " . Auth::id(), $output);

            ActivityLog::create([
                'user_id'  => Auth::id(),
                'activity' => 'Menarik Pembaruan Sistem (CI/CD Deployment)',
                'status'   => 'Berhasil'
            ]);

            return redirect()->back()->with('success', 'Duar! 💥 Kode web berhasil ter-update dari GitHub dan bersih dari cache!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Deployment gagal: " . $e->getMessage());
            
            ActivityLog::create([
                'user_id'  => Auth::id(),
                'activity' => 'Gagal Menarik Pembaruan Sistem',
                'status'   => 'Gagal'
            ]);

            return redirect()->back()->with('error', 'Gagal menarik pembaruan: ' . $e->getMessage());
        }
    }
}