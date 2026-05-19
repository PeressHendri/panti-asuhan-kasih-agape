<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Child;
use Illuminate\Support\Facades\DB;

class SyncCsvData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:sync-anak';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi database anak (1-31) dari file data_anak.csv dataset VGG16';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai sinkronisasi data anak...");

        $csvFile = base_path('recognition_engine/dataset/data_anak.csv');
        if (!file_exists($csvFile)) {
            $this->error("File CSV tidak ditemukan: " . $csvFile);
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Child::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $lines = file($csvFile);
        $headers = str_getcsv(array_shift($lines));
        $count = 0;

        foreach($lines as $line) {
            if(trim($line) == '') continue;
            $row = str_getcsv($line);
            
            $id = (int)$row[0];
            $nama = trim($row[1]);
            $asal = trim($row[3]);
            $tgl_lahir_raw = trim($row[4]); 
            
            $tgl_lahir = null;
            if($tgl_lahir_raw) {
                $parts = explode('-', $tgl_lahir_raw);
                if(count($parts) == 3) {
                    $tgl_lahir = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; 
                }
            }
            
            $child = new Child();
            $child->id = $id;
            $child->nama = $nama;
            $child->asal_daerah = $asal;
            $child->tanggal_lahir = $tgl_lahir;
            $child->jenis_kelamin = 'Laki-laki'; // Default
            $child->sekolah = '-';
            $child->keterangan = 'Sinkronisasi VGG16';
            $child->save();
            
            $count++;
        }

        $this->info("SUKSES! $count anak berhasil disinkronkan ke database dengan ID persis seperti dataset.");
    }
}
