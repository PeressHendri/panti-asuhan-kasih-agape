<?php
session_start();
// Bypass Nginx buffering/caching
header('X-Accel-Buffering: no');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- BASE PATH AWAL (perlu untuk baca .env) ---
$_BASE_PATH_EARLY = dirname(__DIR__);

// --- BACA PASSWORD DARI .env (bukan hardcode di sini!) ---
// Tambahkan di .env server: SETUP_PANEL_PASSWORD=kata_sandi_rahasia_kamu
function readEnvPassword(string $basePath): string {
    $envFile = $basePath . '/.env';
    if (!file_exists($envFile)) return '';
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2 && trim($parts[0]) === 'SETUP_PANEL_PASSWORD') {
            return trim($parts[1], " \t\"'");
        }
    }
    return ''; // Kosong = akses ditolak
}
define('ACCESS_PASSWORD', readEnvPassword($_BASE_PATH_EARLY));

// --- BASE PATH: Gunakan __DIR__ agar selalu akurat di server manapun ---
// setup.php ada di public/, jadi base Laravel = satu level di atas
define('BASE_PATH', dirname(__DIR__));

// --- AUTO-DETECT PHP BINARY ---
function getPhpBinary() {
    // Coba berbagai lokasi umum
    $candidates = [PHP_BINARY, 'php', 'php8.3', 'php8.2', 'php8.1', 'php8.0', '/usr/bin/php'];
    foreach ($candidates as $bin) {
        $test = @shell_exec($bin . ' --version 2>&1');
        if ($test && strpos($test, 'PHP') !== false) {
            return $bin;
        }
    }
    return 'php'; // fallback
}
define('PHP_BIN', getPhpBinary());

// ---------------------

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: setup.php");
    exit;
}

$error = "";
if (isset($_POST['password'])) {
    if ($_POST['password'] === ACCESS_PASSWORD) {
        $_SESSION['authenticated'] = true;
    } else {
        $error = "Sandi tidak valid!";
    }
}

$isAuthenticated = $_SESSION['authenticated'] ?? false;

// --- UTILITIES ---
function getPhpVersion() {
    return phpversion();
}

function checkDirPerm($path) {
    $fullPath = BASE_PATH . '/' . $path;
    return is_writable($fullPath);
}

function checkSymlink() {
    // Cek symlink dari public/storage ke storage/app/public
    return is_link(__DIR__ . '/storage');
}

function getDiskInfo() {
    $total = @disk_total_space("/");
    $free  = @disk_free_space("/");
    if (!$total) return ['total'=>0,'free'=>0,'used'=>0,'percent'=>0];
    $used    = $total - $free;
    $percent = ($total > 0) ? ($used / $total) * 100 : 0;
    return [
        'total'   => round($total / 1024 / 1024 / 1024, 1),
        'free'    => round($free  / 1024 / 1024 / 1024, 1),
        'used'    => round($used  / 1024 / 1024 / 1024, 1),
        'percent' => round($percent, 1),
    ];
}

function getRamInfo() {
    if (!is_readable('/proc/meminfo')) return null;
    $data = file_get_contents('/proc/meminfo');
    preg_match('/MemTotal:\s+(\d+)/', $data, $totalMatch);
    preg_match('/MemAvailable:\s+(\d+)/', $data, $availMatch);
    $total = isset($totalMatch[1]) ? (int)$totalMatch[1] / 1024 / 1024 : 0;
    $avail = isset($availMatch[1]) ? (int)$availMatch[1] / 1024 / 1024 : 0;
    if ($total > 0) {
        $used    = $total - $avail;
        $percent = ($used / $total) * 100;
        return [
            'total'   => round($total, 2),
            'free'    => round($avail, 2),
            'used'    => round($used, 2),
            'percent' => round($percent, 1),
        ];
    }
    return null;
}

function testDbConnection() {
    $envFile = BASE_PATH . '/.env';
    if (!file_exists($envFile)) return ['ok' => false, 'msg' => '.env tidak ada di: ' . $envFile];

    $lines  = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $config = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $config[trim($parts[0])] = trim($parts[1], " \t\n\r\0\x0B\"'");
        }
    }

    try {
        $mysqli = @new mysqli(
            $config['DB_HOST']     ?? '127.0.0.1',
            $config['DB_USERNAME'] ?? '',
            $config['DB_PASSWORD'] ?? '',
            $config['DB_DATABASE'] ?? '',
            (int)($config['DB_PORT'] ?? 3306)
        );
        if ($mysqli->connect_error) {
            return ['ok' => false, 'msg' => $mysqli->connect_error];
        }
        return ['ok' => true, 'msg' => 'Terhubung ke ' . ($config['DB_DATABASE'] ?? '?')];
    } catch (Exception $e) {
        return ['ok' => false, 'msg' => $e->getMessage()];
    }
}

// Jalankan command di BASE_PATH (bukan relative ../)
function runCmd($cmd) {
    $base = BASE_PATH;
    $php  = PHP_BIN;
    // Ganti placeholder
    $cmd  = str_replace('{PHP}', $php, $cmd);
    $full = "cd " . escapeshellarg($base) . " && " . $cmd . " 2>&1";
    return htmlspecialchars(shell_exec($full) ?? '(Tidak ada output)');
}

$action = $_GET['action'] ?? null;
$output = "";

if ($isAuthenticated) {
    set_time_limit(300);

    // Custom Terminal
    if (isset($_POST['custom_cmd']) && trim($_POST['custom_cmd']) !== '') {
        $cmd = trim($_POST['custom_cmd']);
        $output .= "🚀 Perintah: " . htmlspecialchars($cmd) . "\n";
        $output .= str_repeat("─", 50) . "\n";
        $output .= runCmd($cmd) . "\n";
        $output .= str_repeat("─", 50) . "\n";
        $output .= "✅ Selesai.\n";
    }

    if ($action == 'self_destruct') {
        unlink(__FILE__);
        header("Location: /");
        exit;
    }

    if ($action == 'env_info') {
        // Tampilkan info environment (tanpa password)
        $output .= "📋 ENVIRONMENT INFO\n" . str_repeat("─", 50) . "\n";
        $output .= "BASE_PATH : " . BASE_PATH . "\n";
        $output .= "PHP       : " . PHP_BIN . " (" . phpversion() . ")\n";
        $output .= "CWD       : " . getcwd() . "\n";
        $output .= "IS_WRITABLE storage: " . (is_writable(BASE_PATH . '/storage') ? 'YES' : 'NO') . "\n";
        $output .= "IS_WRITABLE bootstrap/cache: " . (is_writable(BASE_PATH . '/bootstrap/cache') ? 'YES' : 'NO') . "\n";
        $output .= ".env exists: " . (file_exists(BASE_PATH . '/.env') ? 'YES' : 'NO ⚠️') . "\n";
        $output .= "storage symlink: " . (is_link(__DIR__ . '/storage') ? 'YES (OK)' : 'NO ⚠️') . "\n";
        $output .= "\nDB Check: ";
        $db = testDbConnection();
        $output .= ($db['ok'] ? '✅ ' : '❌ ') . $db['msg'] . "\n";
    } elseif ($action == 'fix_perms') {
        $output .= "🔧 Memperbaiki Izin Folder...\n" . str_repeat("─", 50) . "\n";
        $output .= runCmd("chmod -R 775 storage bootstrap/cache") . "\n";
        $currentUser = trim(shell_exec('whoami') ?? 'www-data');
        $output .= runCmd("chown -R " . escapeshellarg($currentUser) . ":www-data storage bootstrap/cache") . "\n";
        $output .= "✅ Selesai.\n";
    } elseif ($action == 'view_logs') {
        $logPath = BASE_PATH . '/storage/logs/laravel.log';
        if (file_exists($logPath)) {
            $output .= "📄 50 Baris Terakhir Log Laravel:\n" . str_repeat("─", 50) . "\n";
            $output .= htmlspecialchars(shell_exec("tail -n 50 " . escapeshellarg($logPath)));
        } else {
            $output .= "❌ File log tidak ditemukan di: " . $logPath;
        }
    } elseif ($action == 'deploy') {
        $output .= "🚀 CI/CD Deployment GitHub...\n" . str_repeat("─", 50) . "\n";
        $output .= "🏃 [1/5] git pull origin main...\n";
        $output .= runCmd("git pull origin main") . "\n";
        $output .= "🏃 [2/5] composer install...\n";
        $output .= runCmd("COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --no-ansi --no-dev --prefer-dist --optimize-autoloader") . "\n";
        $output .= "🏃 [3/5] artisan optimize:clear...\n";
        $output .= runCmd("{PHP} artisan optimize:clear --no-ansi") . "\n";
        $output .= "🏃 [4/5] artisan migrate --force...\n";
        $output .= runCmd("{PHP} artisan migrate --force --no-ansi") . "\n";
        $output .= "🏃 [5/5] artisan storage:link...\n";
        $output .= runCmd("{PHP} artisan storage:link --no-ansi") . "\n";
        $output .= "\n✅ Deployment Selesai!\n";
    } elseif ($action == 'clear_cache') {
        $cmds = ['optimize:clear', 'cache:clear', 'config:clear', 'view:clear', 'route:clear'];
        foreach ($cmds as $cmd) {
            $output .= "🏃 artisan $cmd...\n";
            $output .= runCmd("{PHP} artisan $cmd --no-ansi") . "\n";
        }
        $output .= "✅ Semua cache berhasil dibersihkan!\n";
    } elseif ($action == 'storage_link') {
        $output .= "🔗 Membuat Storage Link...\n" . str_repeat("─", 50) . "\n";
        $output .= runCmd("{PHP} artisan storage:link --no-ansi") . "\n";
        $output .= "Symlink check: " . (is_link(__DIR__ . '/storage') ? '✅ OK' : '❌ Gagal') . "\n";
    } elseif ($action == 'full_setup') {
        if (file_exists(__DIR__ . '/index.html')) unlink(__DIR__ . '/index.html');
        $cmds = ['key:generate', 'storage:link', 'migrate:fresh --seed', 'optimize:clear'];
        foreach ($cmds as $cmd) {
            $output .= "🏃 artisan $cmd...\n";
            $output .= runCmd("{PHP} artisan $cmd --no-ansi") . "\n";
        }
        $output .= "✅ Full Setup Selesai!\n";
    }
}

// Hanya jalankan fungsi berat setelah authenticated
$disk        = null;
$ram         = null;
$dbStatus    = ['ok' => false, 'msg' => 'Not checked'];

if ($isAuthenticated) {
    $disk     = getDiskInfo();
    $ram      = getRamInfo();
    $dbStatus = testDbConnection();
    $dbConnected = $dbStatus['ok'];
} else {
    $dbConnected = false;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Panel | Agape</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --terminal: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: var(--bg);
            color: var(--text);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .container { width: 100%; max-width: 1200px; margin: 0 auto; }

        .card {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .header h1 { font-size: 1rem; font-weight: 700; }

        .layout { display: grid; grid-template-columns: 320px 1fr; }

        .sidebar { padding: 20px; border-right: 1px solid var(--border); }

        .main {
            padding: 20px;
            background: #fcfcfd;
            display: flex;
            flex-direction: column;
        }

        /* Stat grid */
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }

        .stat-item {
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }

        .stat-label { font-size: 0.6rem; color: var(--muted); text-transform: uppercase; font-weight: 700; }

        .stat-value {
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Progress */
        .progress-group { margin-bottom: 12px; }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .progress-bar-bg {
            width: 100%;
            height: 5px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill { height: 100%; transition: width 0.5s ease; border-radius: 10px; }

        /* Buttons */
        .btn {
            display: block;
            width: 100%;
            padding: 9px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: 0.2s;
            margin-bottom: 6px;
        }

        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #059669; }

        .btn-outline {
            border-color: var(--border);
            color: var(--text);
            background: #fff;
        }

        .btn-outline:hover { background: #f8fafc; }

        .btn-danger-lite {
            color: var(--danger);
            font-size: 0.7rem;
            border-bottom: 1px dotted var(--danger);
            width: fit-content;
            margin: 16px auto 0;
            text-decoration: none;
            display: block;
            text-align: center;
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
            cursor: pointer;
        }

        /* Terminal */
        .terminal {
            background: var(--terminal);
            color: #7dd3fc;
            padding: 18px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            flex: 1;
            min-height: 380px;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.3);
            line-height: 1.6;
        }

        .terminal-input-group { display: flex; gap: 8px; margin-top: 12px; }

        .terminal-input {
            flex: 1;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid var(--terminal);
            background: #1e293b;
            color: #7dd3fc;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            outline: none;
        }

        .terminal-input:focus { border-color: var(--primary); background: var(--terminal); }
        .terminal-input::placeholder { color: #475569; }

        .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .bg-success { background: var(--success); }
        .bg-danger  { background: var(--danger); }
        .bg-warning { background: var(--warning); }

        /* Section divider */
        .section-title {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 14px 0 8px;
        }

        /* Login */
        .login-box { max-width: 350px; margin: 0 auto; padding: 30px; text-align: center; }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            margin-bottom: 12px;
            text-align: center;
            outline: none;
            font-size: 0.9rem;
        }

        .info-box {
            font-size: 0.68rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
            color: #065f46;
        }

        .info-box.warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        @media (max-width: 850px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { border-right: none; border-bottom: 1px solid var(--border); }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!$isAuthenticated): ?>
            <div class="card login-box">
                <h2 style="margin-bottom: 8px;">🔒 Server Panel</h2>
                <p style="font-size: 0.8rem; color: var(--muted); margin-bottom: 20px;">Panti Asuhan Kasih Agape</p>
                <form method="POST">
                    <?php if ($error): ?>
                        <p style="color:var(--danger); font-size:0.75rem; margin-bottom:10px;"><?= $error ?></p>
                    <?php endif; ?>
                    <input type="password" name="password" placeholder="Passcode" required autofocus>
                    <button type="submit" class="btn btn-primary">Masuk ke Control Panel</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="header">
                    <h1>⚙️ SERVER CONTROL PANEL &nbsp;<small style="font-weight:400;color:var(--muted);font-size:0.75rem;"><?= BASE_PATH ?></small></h1>
                    <div style="font-size: 0.75rem; font-weight: 600; display:flex; gap: 12px;">
                        <a href="?action=env_info" style="text-decoration:none; color: var(--muted);">🔍 Info</a>
                        <a href="/" style="text-decoration:none; color: var(--muted);">Home ↗</a>
                        <a href="?logout=1" style="color: var(--danger); text-decoration:none;">Logout</a>
                    </div>
                </div>

                <div class="layout">
                    <!-- SIDEBAR -->
                    <div class="sidebar">
                        <!-- Status Cards -->
                        <div class="stat-grid">
                            <div class="stat-item">
                                <div class="stat-label">PHP</div>
                                <div class="stat-value"><?= getPhpVersion() ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Database</div>
                                <div class="stat-value">
                                    <div class="dot <?= $dbConnected ? 'bg-success' : 'bg-danger' ?>"></div>
                                    <?= $dbConnected ? 'Ready' : 'Error' ?>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Symlink</div>
                                <div class="stat-value">
                                    <div class="dot <?= checkSymlink() ? 'bg-success' : 'bg-danger' ?>"></div>
                                    <?= checkSymlink() ? 'Active' : 'Missing' ?>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Perms</div>
                                <div class="stat-value">
                                    <div class="dot <?= checkDirPerm('storage') ? 'bg-success' : 'bg-danger' ?>"></div>
                                    <?= checkDirPerm('storage') ? 'Safe' : 'Fix!' ?>
                                </div>
                            </div>
                        </div>

                        <!-- Resource Usage -->
                        <div class="progress-group">
                            <div class="progress-label">
                                <span>RAM</span>
                                <span><?= $ram['used'] ?? 0 ?> / <?= $ram['total'] ?? 0 ?> GB (<?= $ram['percent'] ?? 0 ?>%)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill"
                                    style="width:<?= $ram['percent'] ?? 0 ?>%;
                                    background:<?= ($ram['percent'] ?? 0) > 80 ? 'var(--danger)' : 'var(--primary)' ?>;"></div>
                            </div>
                        </div>

                        <div class="progress-group" style="margin-bottom: 20px;">
                            <div class="progress-label">
                                <span>Disk</span>
                                <span><?= $disk['used'] ?? 0 ?> / <?= $disk['total'] ?? 0 ?> GB (<?= $disk['percent'] ?? 0 ?>%)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill"
                                    style="width:<?= $disk['percent'] ?? 0 ?>%;
                                    background:<?= ($disk['percent'] ?? 0) > 80 ? 'var(--danger)' : 'var(--success)' ?>;"></div>
                            </div>
                        </div>

                        <?php if (!$dbConnected): ?>
                        <div class="info-box warning">⚠️ DB Error: <?= htmlspecialchars($dbStatus['msg']) ?></div>
                        <?php else: ?>
                        <div class="info-box">✅ <?= htmlspecialchars($dbStatus['msg']) ?></div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="section-title">🚀 Deployment</div>
                        <a href="?action=deploy" class="btn btn-primary"
                            onclick="return confirm('Tarik update dari GitHub & migrate?')">🚀 CI/CD GitHub Deploy</a>

                        <div class="section-title">🔧 Maintenance</div>
                        <a href="?action=clear_cache" class="btn btn-outline">🧹 Clear All Cache</a>
                        <a href="?action=storage_link" class="btn btn-outline">🔗 Buat Storage Link</a>
                        <a href="?action=fix_perms" class="btn btn-outline">🔑 Fix Folder Permissions</a>
                        <a href="?action=view_logs" class="btn btn-outline">📄 View Error Logs</a>
                        <a href="?action=env_info" class="btn btn-outline">🔍 Environment Info</a>

                        <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border);">
                            <a href="?action=full_setup" class="btn btn-outline"
                                style="color: var(--danger); border-color: #fee2e2;"
                                onclick="return confirm('⚠️ Database akan di-RESET dan di-SEED ulang! Semua data hilang! Yakin?')">
                                ⚠️ Full Reset Database</a>
                        </div>
                    </div>

                    <!-- MAIN TERMINAL -->
                    <div class="main">
                        <div class="section-title">💻 Terminal Output</div>
                        <div class="terminal" id="terminal-output"><?= $output ?: "System Ready.\nBase Path : " . BASE_PATH . "\nPHP Binary: " . PHP_BIN . "\n\nKetik perintah di bawah atau pilih aksi dari sidebar..." ?></div>

                        <form method="POST" class="terminal-input-group">
                            <input type="text" name="custom_cmd" class="terminal-input"
                                placeholder="Ketik perintah (contoh: php artisan route:list) lalu Enter..."
                                autocomplete="off" id="cmd-input">
                            <button type="submit" class="btn btn-primary"
                                style="width: auto; padding: 0 18px; margin: 0; white-space:nowrap;">▶ Jalankan</button>
                        </form>

                        <a href="?action=self_destruct" class="btn-danger-lite"
                            onclick="return confirm('File setup.php akan dihapus permanen dari server!')">
                            🗑️ Hapus Panel Setup Secara Permanen
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-scroll terminal ke bawah
        const terminal = document.getElementById('terminal-output');
        if (terminal) terminal.scrollTop = terminal.scrollHeight;

        // Focus input terminal
        const cmdInput = document.getElementById('cmd-input');
        if (cmdInput) cmdInput.focus();
    </script>
</body>

</html>