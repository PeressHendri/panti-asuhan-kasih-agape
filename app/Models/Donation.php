<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_donatur',
        'email',
        'telepon',
        'jenis_donasi',
        'jumlah',
        'keterangan',
        'nomor_resi',
        'bukti_transfer_path',
        'child_id',
        'user_id',
        'status',
        'tanggal',
        'catatan_admin'
    ];

    protected $casts = [
        'id' => 'string',
        'tanggal' => 'date'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
