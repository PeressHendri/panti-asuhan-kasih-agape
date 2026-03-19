<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Child;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = base_path('recognition_engine/data_anak.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan di: $csvPath");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Skip header

        $count = 0;
        while (($row = fgetcsv($file)) !== FALSE) {
            // Bersihkan baris kosong
            if (empty($row) || !isset($row[0])) continue;

            $id_anak = $row[0];
            $nama_lengkap = $row[1] ?? '';
            
            // Logika untuk menangani perbedaan struktur kolom (Baris 1-20 vs 21-22)
            // Cek apakah kolom ke-4 berisi tanggal (format DD-MM-YYYY) atau tempat lahir (String)
            $col4 = $row[3] ?? '';
            $col5 = $row[4] ?? '';
            $col6 = $row[5] ?? '';

            $tanggal_lahir = null;
            $tempat_lahir = null;
            $label = null;

            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $col4)) {
                // Format Baris 1-20: id, nama, panggilan, TANGGAL, LABEL, TGL_AMBIL...
                $tanggal_lahir = $col4;
                $tempat_lahir = 'Ambon'; // Default karena mayoritas dari Ambon di panti ini
                $label = $col5;
            } else {
                // Format Baris 21-22: id, nama, panggilan, TEMPAT, TANGGAL, LABEL...
                $tempat_lahir = $col4 ?: 'Ambon';
                $tanggal_lahir = $col5;
                $label = $col6;
            }

            // Konversi tanggal ke format database (YYYY-MM-DD)
            try {
                if ($tanggal_lahir) {
                    $tanggal_lahir = Carbon::createFromFormat('d-m-Y', $tanggal_lahir)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $tanggal_lahir = null;
            }

            // Update atau buat baru berdasarkan ID Anak atau Nama
            Child::updateOrCreate(
                ['id' => (int)$id_anak],
                [
                    'nama' => $nama_lengkap,
                    'tanggal_lahir' => $tanggal_lahir,
                    'asal_daerah' => $tempat_lahir,
                    'jenis_kelamin' => $this->inferGender($nama_lengkap),
                    'status_sponsor' => false,
                    'tanggal_masuk' => now(),
                    // Simpan label untuk referensi engine
                    'keterangan' => "Label: $label",
                ]
            );
            $count++;
        }

        fclose($file);
        $this->command->info("Berhasil memasukkan $count data anak dari CSV.");
    }

    private function inferGender($name)
    {
        $name = strtolower($name);
        $male_keywords = ['jordan', 'charly', 'eliazer', 'elisa', 'jaydend', 'juan', 'peres', 'pesta', 'ricat', 'stefanus', 'timoty', 'jojo'];
        $female_keywords = ['aprilia', 'misye', 'ester', 'vanesya', 'pita', 'yestien', 'enjelin', 'melinda', 'gabriela', 'prilly', 'rahelia', 'rahel'];

        foreach ($male_keywords as $key) {
            if (str_contains($name, $key)) return 'L';
        }
        foreach ($female_keywords as $key) {
            if (str_contains($name, $key)) return 'P';
        }
        return 'L'; // Default
    }
}
