<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvCamera extends Model
{
    use HasFactory;

    protected $fillable = [
        'kamera_id',
        'nama',
        'rtsp_url',
        'hls_url',
        'is_active',
        'is_online',
        'last_ping',
        'lokasi'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_ping' => 'datetime',
    ];
}
