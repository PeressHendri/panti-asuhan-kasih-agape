<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CctvCamera;
use App\Models\CctvActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'data' => $cameras
        ]);
    }
}
