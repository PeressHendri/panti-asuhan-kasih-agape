<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'sekolah',
        'asal_daerah',
        'tanggal_masuk',
        'keterangan',
        'status_sponsor',
        'nama_sponsor',
        'face_encoding_lbph',
        'face_encoding_cnn',
        'photo'
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'child_id');
    }

    protected $casts = [
        'tanggal_lahir' => 'date'
    ];
}