<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'child_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'note'
    ];

    protected $dates = ['date', 'check_in', 'check_out']; // Opsional: untuk mengelola tanggal sebagai Carbon

    protected $casts = [
        'date' => 'datetime',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ]; // Opsional: untuk tipe data otomatis

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}