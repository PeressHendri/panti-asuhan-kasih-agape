<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetrainLbph extends Command
{
    protected $signature   = 'face:retrain-lbph';
    protected $description = 'Retrain ulang model LBPH dari dataset yang ada di recognition_engine/dataset/train/';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║     LBPH RETRAIN — Kasih Agape       ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');

        $scriptPath = base_path('recognition_engine/scripts/train_lbph.py');

        if (!file_exists($scriptPath)) {
            $this->error("Script tidak ditemukan: $scriptPath");
            return self::FAILURE;
        }

        // Cari Python binary
        $candidates = [
            base_path('venv/bin/python3'),
            '/usr/local/bin/python3',
            '/usr/bin/python3',
            'python3',
        ];
        $pythonBin = 'python3';
        foreach ($candidates as $c) {
            if (str_contains($c, '/') && file_exists($c)) {
                $pythonBin = $c;
                break;
            }
        }

        // PYTHONPATH untuk site-packages lokal
        $homes = ['/home/pantiasuhankasihagape', getenv('HOME') ?: ''];
        $paths = [];
        foreach (array_filter(array_unique($homes)) as $home) {
            foreach (glob($home . '/.local/lib/python3.*/site-packages') as $sp) {
                $paths[] = $sp;
            }
        }
        $env = !empty($paths) ? 'PYTHONPATH=' . implode(':', $paths) . ' ' : '';

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
            $this->info('✓ Retrain berhasil! Model LBPH sudah diperbarui.');
            return self::SUCCESS;
        }

        $this->error('✗ Retrain gagal. Cek output di atas untuk detail.');
        return self::FAILURE;
    }
}
