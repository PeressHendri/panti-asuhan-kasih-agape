<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceRecognitionLog; // Menggunakan model yang sudah ada untuk kemudahan integrasi
use Illuminate\Http\Request;
use Carbon\Carbon;

class FaceLogController extends Controller
{
    /**
     * Menerima log pengenalan wajah dari Python (Raspberry Pi).
     * Format: POST /api/face-log
     */
    public function store(Request $request)
    {
        // Validasi sesuai instruksi user
        $validated = $request->validate([
            'child_id' => 'nullable|integer',
            'confidence' => 'required|numeric',
            'detected_at' => 'required|date',
            'source' => 'required|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048', // Opsional upload gambar
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('face_logs', 'public');
        }

        // Simpan log ke tabel face_recognition_logs
        $log = \App\Models\FaceRecognitionLog::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'child_id' => $validated['child_id'],
            'confidence_score' => $validated['confidence'],
            'waktu_deteksi' => Carbon::parse($validated['detected_at']),
            'kamera_id' => $validated['source'],
            'foto_capture_path' => $imagePath,
            'algoritma' => in_array($validated['source'], ['lbph', 'cnn']) ? $validated['source'] : 'lbph',
            'status' => $validated['child_id'] ? 'check_in' : 'tidak_dikenal'
        ]);

        // Sync with Attendance table
        if ($validated['child_id']) {
            $today = Carbon::parse($validated['detected_at'])->startOfDay();
            $attendance = \App\Models\Attendance::firstOrNew([
                'child_id' => $validated['child_id'],
                'date' => $today
            ]);

            if ($log->status === 'check_in') {
                if (!$attendance->check_in) {
                    $attendance->check_in = $log->waktu_deteksi;
                    $attendance->status = 'hadir';
                }
            } else if ($log->status === 'check_out') {
                $attendance->check_out = $log->waktu_deteksi;
            }

            // Sync AI attributes to Attendance record
            $attendance->kamera_id = $validated['source'];
            $attendance->confidence_score = $validated['confidence'];
            $attendance->algoritma = $log->algoritma;
            if ($imagePath) {
                $attendance->foto_capture_path = $imagePath;
            }
            $attendance->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Face log saved successfully',
            'data' => $log
        ], 201);
    }
}
