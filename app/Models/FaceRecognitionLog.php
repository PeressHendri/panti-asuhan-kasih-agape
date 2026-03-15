<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceRecognitionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'foto_capture_path',
        'confidence_score',
        'algoritma',
        'status',
        'kamera_id',
        'waktu_deteksi'
    ];

    protected $casts = [
        'id' => 'string',
        'waktu_deteksi' => 'datetime'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    // Accessor: nama anak atau "Tidak Dikenal"
    public function getNamaAttribute()
    {
        return $this->child ? $this->child->nama : 'Tidak Dikenal';
    }
}
