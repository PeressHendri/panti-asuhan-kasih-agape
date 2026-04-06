<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceRecognitionLog;
use App\Models\Attendance;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FaceRecognitionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'nullable|exists:children,id',
            'confidence_score' => 'required|numeric',
            'algoritma' => 'required|in:lbph,cnn',
            'status' => 'required|in:check_in,check_out,tidak_dikenal',
            'kamera_id' => 'nullable|string',
            'foto_base64' => 'nullable|string'
        ]);

        $kamera_id = $validated['kamera_id'] ?? 'pintu_masuk_utama';
        $foto_path = null;

        // Process base64 image
        if (!empty($validated['foto_base64'])) {
            $image_parts = explode(";base64,", $validated['foto_base64']);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $filename = 'captures/' . Str::uuid() . '.jpg';
                Storage::disk('public')->put($filename, $image_base64);
                $foto_path = $filename;
            }
        }

        $logId = (string) Str::uuid();

        $log = FaceRecognitionLog::create([
            'id' => $logId,
            'child_id' => $validated['child_id'],
            'confidence_score' => $validated['confidence_score'],
            'algoritma' => $validated['algoritma'],
            'status' => $validated['status'],
            'kamera_id' => $kamera_id,
            'foto_capture_path' => $foto_path,
        ]);

        // Process check-in / check-out
        if ($validated['status'] !== 'tidak_dikenal' && $validated['child_id']) {
            $today = Carbon::today();
            $attendance = Attendance::firstOrNew([
                'child_id' => $validated['child_id'],
                'date' => $today
            ]);

            if ($validated['status'] === 'check_in') {
                if (!$attendance->check_in) {
                    $attendance->check_in = now();
                }
                $attendance->status = 'hadir';
            } else if ($validated['status'] === 'check_out') {
                $attendance->check_out = now();
                // Jika belum ada status, pastikan dianggap hadir karena dia terdeteksi di panti
                if (!$attendance->status) {
                    $attendance->status = 'hadir';
                }
            }

            $attendance->kamera_id = $kamera_id;
            $attendance->confidence_score = $validated['confidence_score'];
            $attendance->algoritma = $validated['algoritma'];
            if ($foto_path) {
                $attendance->foto_capture_path = $foto_path;
            }
            $attendance->save();
        }

        if ($validated['status'] === 'tidak_dikenal') {
            // Bisa tambah notifikasi ke admin di sini sesuai requirement
            // menggunakan notification system laravel
        }

        return response()->json([
            'success' => true,
            'message' => 'Face recognition log saved.',
            'data' => $log
        ]);
    }

    public function today()
    {
        $today = Carbon::today();

        $logs = FaceRecognitionLog::whereDate('waktu_deteksi', $today)->get();

        $total = $logs->count();
        $check_in = $logs->where('status', 'check_in')->count();
        $check_out = $logs->where('status', 'check_out')->count();
        $tidak_dikenal = $logs->where('status', 'tidak_dikenal')->count();

        return response()->json([
            'total' => $total,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'tidak_dikenal' => $tidak_dikenal,
            'logs' => $logs
        ]);
    }

    public function index()
    {
        $logs = FaceRecognitionLog::with('child')->latest('waktu_deteksi')->paginate(50);
        return response()->json($logs);
    }
}
