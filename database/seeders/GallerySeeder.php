<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $galleries = [
            [
                'title' => 'Bakti Sosial',
                'description' => 'Kegiatan bakti sosial bersama komunitas',
                'image' => 'assets/img/Bakti-Sosial1.jpg'
            ],
            [
                'title' => 'Kebersamaan',
                'description' => 'Foto bersama para donatur di Panti',
                'image' => 'assets/img/HDCI.jpg'
            ],
            [
                'title' => 'Foto Bersama',
                'description' => 'Kebahagiaan anak-anak panti asuhan',
                'image' => 'assets/img/anak-anak-panti-asuhan-kasih-agape-saat-berfoto-bersama-di-depan-artotel.jpg'
            ],
            [
                'title' => 'Berbagi Kasih',
                'description' => 'Momen kehangatan saat berbagi kasih',
                'image' => 'assets/img/berbagikasih.jpg'
            ],
            [
                'title' => 'Perayaan Natal',
                'description' => 'Natal penuh sukacita bersama pengurus',
                'image' => 'assets/img/natal2022panti.jpeg'
            ],
            [
                'title' => 'Kegiatan Outdoor',
                'description' => 'Anak-anak asuh saat acara Pakuwon',
                'image' => 'assets/img/cewe-pcm.jpg'
            ],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create($gallery);
        }
    }
}
