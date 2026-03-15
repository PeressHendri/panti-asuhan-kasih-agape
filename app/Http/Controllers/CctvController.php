<?php

namespace App\Http\Controllers;

use App\Models\CctvCamera;
use App\Models\CctvActivityLog;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    public function index()
    {
        $cameras = CctvCamera::where('is_active', true)->get();
        $activityLogs = CctvActivityLog::with('camera')
            ->latest('waktu')
            ->paginate(20);

        return view('dashboard.cctv.index', compact('cameras', 'activityLogs'));
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
