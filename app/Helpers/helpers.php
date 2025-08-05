<?php

use App\Models\ActivityLog;

if (!function_exists('log_activity')) {
    function log_activity($activity, $status = 'Berhasil') {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => $activity,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            \Log::error("Gagal mencatat aktivitas: " . $e->getMessage());
        }
    }
}
