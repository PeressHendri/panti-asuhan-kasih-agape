<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'kamera_id',
        'jenis_aktivitas',
        'keterangan',
        'snapshot_path',
        'waktu'
    ];

    protected $casts = [
        'id' => 'string',
        'waktu' => 'datetime',
    ];

    public function camera()
    {
        return $this->belongsTo(CctvCamera::class, 'kamera_id', 'kamera_id');
    }
}
