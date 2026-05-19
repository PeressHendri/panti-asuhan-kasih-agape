<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\FaceRecognitionLog;

class UpdateLbphFromCaptures extends Command
{
    protected $signature   = 'face:update-lbph';
    protected $description = 'Update/Retrain model LBPH dari foto captures yang sudah berhasil teridentifikasi di database';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔═══════════════════════════════════════════════╗');
        $this->info('║   UPDATE LBPH — Semua Anak dari Captures      ║');
        $this->info('╚═══════════════════════════════════════════════╝');
        $this->info('');

        // ── 1. Ambil semua log yang punya foto capture & child_id ─────────
        $logs = FaceRecognitionLog::select('child_id', 'foto_capture_path')
            ->whereNotNull('child_id')
            ->whereNotNull('foto_capture_path')
            ->where('foto_capture_path', 'like', 'captures/web_%')
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('Tidak ada log face recognition dengan foto capture yang ditemukan.');
            $this->warn('Pastikan beberapa anak sudah pernah berhasil scan wajah terlebih dahulu.');
            return self::FAILURE;
        }

        $this->info("  Ditemukan {$logs->count()} log foto capture.");

        // ── 2. Buat mapping: {full_path: child_id} ─────────────────────────
        $mapping = [];
        $storagePath = storage_path('app/public');
        $skipped     = 0;

        foreach ($logs as $log) {
            $fullPath = $storagePath . '/' . $log->foto_capture_path;
            if (file_exists($fullPath)) {
                // Simpan mapping path absolut → child_id
                $mapping[$fullPath] = (int) $log->child_id;
            } else {
                $skipped++;
            }
        }

        $this->info("  {$skipped} foto tidak ditemukan di storage (sudah dihapus).");
        $this->info("  " . count($mapping) . " foto siap diproses.\n");

        if (empty($mapping)) {
            $this->error('Tidak ada foto valid yang bisa diproses.');
            return self::FAILURE;
        }

        // ── 3. Simpan mapping ke file temp JSON ───────────────────────────
        $tmpJson = storage_path('app/lbph_update_mapping.json');
        file_put_contents($tmpJson, json_encode($mapping, JSON_PRETTY_PRINT));
        $this->line("  Mapping disimpan ke: $tmpJson");

        // ── 4. Siapkan Python binary yang PUNYA cv2.face ─────────────────
        // Prioritaskan python yang bisa import cv2.face (opencv-contrib)
        $homes = ['/home/pantiasuhankasihagape', getenv('HOME') ?: ''];
        $paths = [];
        foreach (array_filter(array_unique($homes)) as $home) {
            foreach (glob($home . '/.local/lib/python3.*/site-packages') as $sp) {
                $paths[] = $sp;
            }
        }
        $env = !empty($paths) ? 'PYTHONPATH=' . implode(':', $paths) . ' ' : '';

        // Cari Python yang PUNYA cv2.face
        $candidates = [
            base_path('venv/bin/python3'),
            base_path('venv/bin/python'),
            '/usr/local/bin/python3',
            '/usr/bin/python3',
            'python3',
        ];
        $pythonBin = null;
        foreach ($candidates as $c) {
            $exists = str_contains($c, '/') ? file_exists($c) : !empty(trim((string)shell_exec("which $c 2>/dev/null")));
            if (!$exists) continue;
            $bin = str_contains($c, '/') ? $c : trim((string)shell_exec("which $c 2>/dev/null"));
            // Cek apakah python ini punya cv2.face
            $test = shell_exec($env . escapeshellarg($bin) . " -c \"import cv2; cv2.face.LBPHFaceRecognizer_create(); print('ok')\" 2>/dev/null");
            if (trim((string)$test) === 'ok') {
                $pythonBin = $bin;
                $this->line("  Python binary   : $pythonBin (cv2.face ✓)");
                break;
            }
        }

        if (!$pythonBin) {
            $this->error('Tidak ada Python yang memiliki cv2.face (opencv-contrib-python).');
            $this->warn('Jalankan perintah berikut untuk install:');
            $this->line('  pip3 install --user opencv-contrib-python-headless');
            $this->line('  atau: python3 -m pip install --user opencv-contrib-python-headless');
            return self::FAILURE;
        }

        // ── 5. Jalankan Python script ──────────────────────────────────────
        $scriptPath = base_path('recognition_engine/scripts/update_all_lbph.py');
        $cmd        = $env . escapeshellarg($pythonBin) . ' ' . escapeshellarg($scriptPath)
                    . ' ' . escapeshellarg($tmpJson) . ' 2>&1';

        $this->info("\n  Menjalankan update LBPH...\n");

        $handle = popen($cmd, 'r');
        if (!$handle) {
            $this->error('Gagal menjalankan Python script.');
            return self::FAILURE;
        }

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                $this->line(rtrim($line));
            }
        }

        $exitCode = pclose($handle);

        // Hapus file temp
        if (file_exists($tmpJson)) {
            unlink($tmpJson);
        }

        $this->info('');
        if ($exitCode === 0) {
            $this->info('✓ Update LBPH berhasil! Semua anak diperbarui.');
            return self::SUCCESS;
        }

        $this->error('✗ Update LBPH gagal. Lihat output di atas.');
        return self::FAILURE;
    }
}
