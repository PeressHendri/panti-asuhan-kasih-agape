<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CctvCamera;
use App\Models\CctvActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * CctvController - Menangani semua request API dari Raspberry Pi terkait CCTV.
 *
 * [GAP 3 FIX] Ditambahkan method yoloLog() untuk menerima data dari
 * yolo_activity_tracker.py yang mengirimkan data aktivitas ruang bersama.
 */

class CctvController extends Controller
{
    public function logActivity(Request $request)
    {
        $validated = $request->validate([
            'kamera_id' => 'required|exists:cctv_cameras,kamera_id',
            'jenis_aktivitas' => 'required|in:motion_detected,object_tracked,camera_online,camera_offline',
            'keterangan' => 'nullable|string',
            'snapshot_base64' => 'nullable|string'
        ]);

        $snapshot_path = null;

        if (!empty($validated['snapshot_base64'])) {
            $image_parts = explode(";base64,", $validated['snapshot_base64']);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $filename = 'captures/cctv_' . Str::uuid() . '.jpg';
                Storage::disk('public')->put($filename, $image_base64);
                $snapshot_path = $filename;
            }
        }

        $logId = (string) Str::uuid();

        $log = CctvActivityLog::create([
            'id' => $logId,
            'kamera_id' => $validated['kamera_id'],
            'jenis_aktivitas' => $validated['jenis_aktivitas'],
            'keterangan' => $validated['keterangan'] ?? '',
            'snapshot_path' => $snapshot_path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity logged.',
            'data' => $log
        ]);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'kamera_id' => 'required|exists:cctv_cameras,kamera_id',
            'is_online' => 'required|boolean',
            'last_ping' => 'nullable|date'
        ]);

        $camera = CctvCamera::where('kamera_id', $validated['kamera_id'])->firstOrFail();

        $camera->is_online = $validated['is_online'];
        if (isset($validated['last_ping'])) {
            $camera->last_ping = $validated['last_ping'];
        } else if ($validated['is_online']) {
            $camera->last_ping = now();
        }

        $camera->save();

        return response()->json([
            'success' => true,
            'message' => 'Camera status updated.',
            'data' => $camera
        ]);
    }

    public function getCameras()
    {
        $cameras = CctvCamera::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data'    => $cameras
        ]);
    }

    /**
     * [GAP 3 FIX] yoloLog - Menerima data aktivitas dari YOLOv8 Activity Tracker.
     *
     * Payload yang diterima dari yolo_activity_tracker.py:
     *   kamera_id       : ID kamera sumber (string)
     *   jenis_aktivitas : 'object_tracked'
     *   keterangan      : JSON string berisi total_anak, status_ruangan, detail_aktivitas
     *
     * Metode ini menggunakan endpoint yang SAMA dengan logActivity() (/api/cctv/activity).
     * Data YOLO sudah di-format ulang di Python agar cocok dengan skema validasi ini.
     * Method ini disediakan sebagai alias eksplisit untuk endpoint /api/cctv/yolo-log
     * sehingga bisa dibedakan di log server.
     */
    public function yoloLog(Request $request)
    {
        $validated = $request->validate([
            'kamera_id'              => 'required|string|max:50',
            'total_anak_terdeteksi'  => 'required|integer|min:0',
            'status_ruangan'         => 'required|string|max:100',
            'detail_aktivitas'       => 'required|array',
        ]);

        $kamera_id = $validated['kamera_id'];

        // Pastikan kamera ID ada di database, buat otomatis jika belum ada
        // (agar tidak gagal karena foreign key constraint saat pertama kali jalan)
        CctvCamera::firstOrCreate(
            ['kamera_id' => $kamera_id],
            [
                'nama'      => 'Ruang Bersama (YOLO Auto)',
                'lokasi'    => 'Ruang Bersama',
                'is_active' => true,
                'is_online' => true,
            ]
        );

        $log = CctvActivityLog::create([
            'id'              => (string) Str::uuid(),
            'kamera_id'       => $kamera_id,
            'jenis_aktivitas' => 'object_tracked',
            'keterangan'      => json_encode([
                'total_anak'      => $validated['total_anak_terdeteksi'],
                'status_ruangan'  => $validated['status_ruangan'],
                'detail'          => $validated['detail_aktivitas'],
                'timestamp'       => now()->toDateTimeString(),
            ], JSON_UNESCAPED_UNICODE),
        ]);

        // Perbarui status online kamera
        CctvCamera::where('kamera_id', $kamera_id)->update([
            'is_online' => true,
            'last_ping' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'YOLO activity log saved.',
            'data'    => $log
        ]);
    }
}
