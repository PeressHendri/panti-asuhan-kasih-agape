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

        // Simpan log ke tabel face_recognition_logs (atau buat tabel face_logs baru jika diinginkan)
        // Disini kita gunakan tabel yang sudah ada agar data tetap sinkron dengan dashboard Filament
        $log = FaceRecognitionLog::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'child_id' => $validated['child_id'],
            'confidence_score' => $validated['confidence'],
            'waktu_deteksi' => Carbon::parse($validated['detected_at']),
            'kamera_id' => $validated['source'],
            'foto_capture_path' => $imagePath,
            'algoritma' => $validated['source'] === 'lbph' ? 'lbph' : 'cnn', // Deteksi nama algoritma dari source jika perlu
            'status' => $validated['child_id'] ? 'check_in' : 'tidak_dikenal'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Face log saved successfully',
            'data' => $log
        ], 201);
    }
}
