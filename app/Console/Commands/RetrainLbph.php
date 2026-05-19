<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetrainLbph extends Command
{
    protected $signature   = 'face:retrain-lbph';
    protected $description = 'Retrain ulang model LBPH dari dataset di recognition_engine/dataset/train/';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║     LBPH FULL RETRAIN — Kasih Agape          ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');

        $scriptPath  = base_path('recognition_engine/scripts/train_lbph.py');
        $datasetPath = base_path('recognition_engine/dataset/train');

        // ── Cek script ────────────────────────────────────────────────────
        if (!file_exists($scriptPath)) {
            $this->error("Script tidak ditemukan: $scriptPath");
            return self::FAILURE;
        }

        // ── Info dataset ──────────────────────────────────────────────────
        if (is_dir($datasetPath)) {
            $folders     = array_filter(glob($datasetPath . '/*'), 'is_dir');
            $totalPhotos = 0;
            foreach ($folders as $f) {
                $totalPhotos += count(glob($f . '/*.{jpg,jpeg,png}', GLOB_BRACE));
            }
            $this->info('  Dataset path  : ' . $datasetPath);
            $this->info('  Jumlah kelas  : ' . count($folders) . ' orang');
            $this->info("  Total foto    : $totalPhotos foto");
            $this->info('');
        } else {
            $this->error("Folder dataset tidak ditemukan: $datasetPath");
            return self::FAILURE;
        }

        // ── PYTHONPATH untuk site-packages lokal ──────────────────────────
        $homes = ['/home/pantiasuhankasihagape', getenv('HOME') ?: ''];
        $paths = [];
        foreach (array_filter(array_unique($homes)) as $home) {
            foreach (glob($home . '/.local/lib/python3.*/site-packages') as $sp) {
                $paths[] = $sp;
            }
        }
        $env = !empty($paths) ? 'PYTHONPATH=' . implode(':', $paths) . ' ' : '';

        // ── Cari Python yang PUNYA cv2.face ───────────────────────────────
        $candidates = [
            base_path('venv/bin/python3'),
            base_path('venv/bin/python'),
            '/usr/local/bin/python3',
            '/usr/bin/python3',
            'python3',
        ];
        $pythonBin = null;
        foreach ($candidates as $c) {
            $bin = str_contains($c, '/') ? $c : trim((string) shell_exec("which $c 2>/dev/null"));
            if (empty($bin) || (str_contains($c, '/') && !file_exists($c))) continue;
            $test = shell_exec($env . escapeshellarg($bin) . " -c \"import cv2; cv2.face.LBPHFaceRecognizer_create(); print('ok')\" 2>/dev/null");
            if (trim((string)$test) === 'ok') {
                $pythonBin = $bin;
                $this->info("  Python binary : $pythonBin (cv2.face ✓)");
                break;
            }
        }

        if (!$pythonBin) {
            $this->error('Tidak ada Python yang memiliki cv2.face!');
            $this->warn('Install dulu dengan:');
            $this->line('  pip3 install --user opencv-contrib-python-headless');
            return self::FAILURE;
        }

        // ── Jalankan retrain ──────────────────────────────────────────────
        $this->info('');
        $this->info('  Memulai retrain semua anak...');
        $this->info('');

        $cmd    = $env . escapeshellarg($pythonBin) . ' ' . escapeshellarg($scriptPath) . ' 2>&1';
        $handle = popen($cmd, 'r');

        if (!$handle) {
            $this->error('Gagal menjalankan script Python.');
            return self::FAILURE;
        }

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                $this->line(rtrim($line));
            }
        }

        $exitCode = pclose($handle);
        $this->info('');

        if ($exitCode === 0) {
            $this->info('✓ Retrain berhasil! Model LBPH diperbarui untuk semua anak.');
            return self::SUCCESS;
        }

        $this->error('✗ Retrain gagal. Cek output di atas.');
        return self::FAILURE;
    }
}
