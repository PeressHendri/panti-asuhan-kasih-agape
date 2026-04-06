<?php

namespace App\Http\Controllers;

use App\Models\CctvCamera;
use App\Models\CctvActivityLog;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    public function index()
    {
        $cameras      = CctvCamera::where('is_active', true)->get();
        $activityLogs = CctvActivityLog::with('camera')
            ->orderBy('created_at', 'desc')
            ->take(30)->get();
        $faceLogs     = \App\Models\FaceRecognitionLog::with('child')
            ->latest('waktu_deteksi')
            ->take(20)->get();

        return view('dashboard.cctv.index', compact('cameras', 'activityLogs', 'faceLogs'));
    }

    /**
     * Endpoint JSON untuk polling AJAX dari halaman CCTV.
     * Mengembalikan status kamera + log terbaru dalam SATU request.
     * Dipanggil setiap 10 detik oleh JavaScript — tanpa reload halaman.
     */
    public function liveData()
    {
        // Status semua kamera aktif
        $cameras = CctvCamera::where('is_active', true)->get()->map(function ($cam) {
            return [
                'kamera_id'  => $cam->kamera_id,
                'nama'       => $cam->nama,
                'is_online'  => $cam->is_online,
                'last_ping'  => $cam->last_ping
                    ? \Carbon\Carbon::parse($cam->last_ping)->diffForHumans()
                    : '-',
            ];
        });

        // 20 log wajah terbaru hari ini
        $faceLogs = \App\Models\FaceRecognitionLog::with('child')
            ->latest('waktu_deteksi')
            ->take(20)
            ->get()
            ->map(function ($log) {
                return [
                    'foto'        => $log->foto_capture_path
                        ? asset('storage/' . $log->foto_capture_path)
                        : null,
                    'nama'        => $log->status === 'tidak_dikenal'
                        ? 'Wajah Asing'
                        : ($log->child->nama ?? 'Seseorang'),
                    'kamera_id'   => strtoupper($log->kamera_id),
                    'waktu'       => \Carbon\Carbon::parse($log->waktu_deteksi)->format('H:i:s'),
                    'status'      => $log->status,
                    'status_label'=> strtoupper(str_replace('_', ' ', $log->status)),
                    'is_unknown'  => $log->status === 'tidak_dikenal',
                ];
            });

        // 30 log aktivitas CCTV terbaru
        $activityLogs = CctvActivityLog::orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($log) {
                return [
                    'waktu'           => \Carbon\Carbon::parse($log->waktu)->format('H:i:s'),
                    'jenis_aktivitas' => $log->jenis_aktivitas,
                    'keterangan'      => $log->keterangan,
                ];
            });

        // Jumlah wajah asing hari ini untuk alert
        $unknownToday = \App\Models\FaceRecognitionLog::where('status', 'tidak_dikenal')
            ->whereDate('waktu_deteksi', today())
            ->count();

        return response()->json([
            'cameras'      => $cameras,
            'face_logs'    => $faceLogs,
            'activity_logs'=> $activityLogs,
            'unknown_today'=> $unknownToday,
            'updated_at'   => now()->format('H:i:s'),
        ]);
    }

    public function refresh($id)
    {
        // Simple manual refresh logic for a camera (placeholder)
        $camera = CctvCamera::findOrFail($id);
        $camera->update(['last_ping' => now()]);

        return redirect()->back()->with('success', 'Kamera berhasil disinkronkan ualang.');
    }

    // --- Admin only CRUD ---

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kamera_id' => 'required|string|unique:cctv_cameras',
            'nama' => 'required|string|max:100',
            'rtsp_url' => 'nullable|string',
            'hls_url' => 'nullable|string',
            'lokasi' => 'nullable|string'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_online'] = false;

        CctvCamera::create($validated);
        return redirect()->back()->with('success', 'Kamera CCTV berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $camera = CctvCamera::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'rtsp_url' => 'nullable|string',
            'hls_url' => 'nullable|string',
            'lokasi' => 'nullable|string'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $camera->update($validated);
        return redirect()->back()->with('success', 'Kamera CCTV berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $camera = CctvCamera::findOrFail($id);
        $camera->delete();
        return redirect()->back()->with('success', 'Kamera CCTV berhasil dihapus.');
    }
}
