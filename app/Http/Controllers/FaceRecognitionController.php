<?php

namespace App\Http\Controllers;

use App\Models\FaceRecognitionLog;
use Illuminate\Http\Request;

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
}
