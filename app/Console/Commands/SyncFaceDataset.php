<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Child;

class SyncFaceDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face:sync-dataset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menyalin semua foto anak ke folder dataset CNN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi foto anak ke dataset CNN...');
        
        $children = Child::whereNotNull('photo')->get();
        $count = 0;

        foreach ($children as $child) {
            $sourcePath = storage_path('app/public/' . $child->photo);
            if (!file_exists($sourcePath)) {
                $this->warn("- Foto asli tidak ditemukan di storage untuk: " . $child->nama);
                continue;
            }

            $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $child->nama);
            $safeName = trim(preg_replace('/_+/', '_', $safeName), '_');
            
            $datasetDir = base_path("recognition_engine/dataset/{$child->id}_{$safeName}");

            if (!file_exists($datasetDir)) {
                mkdir($datasetDir, 0775, true);
            }

            $destPath = $datasetDir . '/ref.jpg';
            copy($sourcePath, $destPath);
            $count++;
            $this->info("✓ Tersinkronisasi: {$child->nama}");
        }

        // Hapus file cache lama jika ada
        $cacheFile = base_path("recognition_engine/dataset/representations_vgg_face.pkl");
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            $this->info('Menghapus cache AI lama...');
        }

        $this->info("=========================================");
        $this->info("SUKSES! $count foto anak berhasil dipindahkan ke mesin CNN.");
        $this->info("=========================================");
    }
}
