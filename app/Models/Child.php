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
        'nim',
        'sekolah',
        'panti_id',
        'photo',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'child_id');
    }

    protected $casts = [
        'tanggal_lahir' => 'date'
    ];
}