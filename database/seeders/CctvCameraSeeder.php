<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CctvCamera;

class CctvCameraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cameras = [
            [
                'kamera_id' => 'ruang_belajar',
                'nama' => 'Ruang Belajar',
                'lokasi' => 'Area belajar anak',
                'is_active' => true,
            ],
            [
                'kamera_id' => 'ruang_ibadah',
                'nama' => 'Ruang Ibadah',
                'lokasi' => 'Ruang ibadah/bersama',
                'is_active' => true,
            ],
            [
                'kamera_id' => 'ruang_bersama',
                'nama' => 'Ruang Bersama',
                'lokasi' => 'Ruang santai/berkumpul',
                'is_active' => true,
            ],
            [
                'kamera_id' => 'halaman',
                'nama' => 'Halaman',
                'lokasi' => 'Area luar/halaman panti',
                'is_active' => true,
            ],
        ];

        foreach ($cameras as $cam) {
            CctvCamera::updateOrCreate(
                ['kamera_id' => $cam['kamera_id']],
                $cam
            );
        }
    }
}
