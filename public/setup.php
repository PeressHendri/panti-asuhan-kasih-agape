<?php
/**
 * Ultimate Secured Laravel VPS Setup & Health Dashboard
 * Panti Asuhan Kasih Agape
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURATION ---
define('ACCESS_PASSWORD', 'agape2026'); 
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

// --- ADVANCED UTILITIES ---
function getPhpVersion() { return phpversion(); }
function checkDirPerm($path) { return is_writable('../' . $path); }
function checkSymlink() { return is_link('storage') ? "✅ Aktif" : "❌ Terputus"; }
function getDiskSpace() {
    $free = disk_free_space("/") / 1024 / 1024 / 1024;
    $total = disk_total_space("/") / 1024 / 1024 / 1024;
    return round($free, 2) . " GB / " . round($total, 2) . " GB";
}
function testDbConnection() {
    try {
        if (!file_exists('../.env')) return "❌ .env Hilang";
        $env = file_get_contents('../.env');
        preg_match('/DB_HOST=(.*)/', $env, $host);
        preg_match('/DB_DATABASE=(.*)/', $env, $db);
        preg_match('/DB_USERNAME=(.*)/', $env, $user);
        preg_match('/DB_PASSWORD=(.*)/', $env, $pass);
        $mysqli = @new mysqli(trim($host[1] ?? '127.0.0.1'), trim($user[1] ?? ''), trim($pass[1] ?? ''), trim($db[1] ?? ''));
        return $mysqli->connect_error ? "❌ Gagal: " . $mysqli->connect_error : "✅ Terhubung";
    } catch (Exception $e) { return "❌ Error"; }
}

$action = $_GET['action'] ?? null;
$output = "";

if ($isAuthenticated && $action) {
    if ($action == 'self_destruct') {
        unlink(__FILE__);
        header("Location: /");
        exit;
    }

    $commands = [
        'full_setup' => ['key:generate', 'storage:link', 'migrate:fresh --seed', 'optimize:clear'],
        'clear_cache' => ['optimize:clear', 'cache:clear', 'config:clear', 'view:clear'],
        'fix_perms' => [],
        'view_logs' => [],
        'deploy' => [],
    ];

    if ($action == 'fix_perms') {
        $output .= "🔧 Memperbaiki Izin Folder...\n";
        $output .= shell_exec("cd .. && chmod -R 775 storage bootstrap/cache 2>&1");
        $output .= "✅ Selesai.\n";
    } elseif ($action == 'view_logs') {
        $logPath = '../storage/logs/laravel.log';
        if (file_exists($logPath)) {
            $output .= "📄 50 Baris Terakhir Log Laravel:\n" . str_repeat("-", 40) . "\n";
            $output .= shell_exec("tail -n 50 " . escapeshellarg($logPath));
        } else {
            $output .= "❌ File log tidak ditemukan.";
        }
    } elseif ($action == 'deploy') {
        // Logika CI/CD Deployment GitHub
        $output .= "🚀 Memulai CI/CD Deployment dari GitHub...\n" . str_repeat("-", 40) . "\n";
        $output .= "🏃 [1/4] Mengambil kode terbaru (git pull)\n";
        $output .= shell_exec("cd .. && git pull origin main 2>&1") . "\n";
        $output .= "🏃 [2/4] Install Dependencies (composer install)\n";
        $output .= shell_exec("cd .. && composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1") . "\n";
        $output .= "🏃 [3/4] Clear Cache (optimize:clear)\n";
        $output .= shell_exec("cd .. && php artisan optimize:clear 2>&1") . "\n";
        $output .= "🏃 [4/4] Migrate Database (migrate --force)\n";
        $output .= shell_exec("cd .. && php artisan migrate --force 2>&1") . "\n";
        $output .= "\n✅ Pembaruan Selesai & Bersih dari Cache!\n";
    } elseif (isset($commands[$action])) {
        if ($action == 'full_setup' && file_exists('index.html')) unlink('index.html');
        foreach ($commands[$action] as $cmd) {
            $output .= "🏃 Eksekusi: php artisan $cmd...\n";
            $output .= shell_exec("cd .. && php artisan $cmd 2>&1") . "\n";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultimate Setup | Agape</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #0ea5e9; --success: #10b981; --bg: #020617; --card: #0f172a; }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: #f1f5f9; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: var(--card); border-radius: 20px; border: 1px solid #1e293b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); overflow: hidden; }
        .head { background: #1e293b; padding: 25px 35px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; }
        .head h1 { font-size: 1.2rem; display: flex; align-items: center; gap: 12px; }
        .content { padding: 35px; }
        .grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; margin-bottom: 30px; }
        .box { background: #02061760; padding: 25px; border-radius: 15px; border: 1px solid #1e293b; }
        .box h3 { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px; }
        .item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem; border-bottom: 1px solid #1e293b30; padding-bottom: 8px; }
        .btn { display: block; width: 100%; padding: 14px; border-radius: 10px; text-decoration: none; text-align: center; font-weight: 600; transition: 0.3s; margin-bottom: 12px; border: none; cursor: pointer; }
        .btn-blue { background: var(--primary); color: white; box-shadow: 0 4px 15px #0ea5e930; }
        .btn-blue:hover { transform: translateY(-2px); background: #38bdf8; }
        .btn-gray { background: #334155; color: #cbd5e1; }
        .btn-red { background: #ef444415; color: #ef4444; border: 1px solid #ef444440; margin-top: 20px; }
        .btn-red:hover { background: #ef4444; color: white; }
        .term { background: #000; color: #38bdf8; padding: 25px; border-radius: 15px; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; line-height: 1.6; max-height: 400px; overflow-y: auto; white-space: pre-wrap; margin-top: 30px; border: 1px solid #0ea5e920; }
        .login-card { max-width: 450px; margin: 100px auto; text-align: center; padding: 40px; }
        input[type="password"] { width: 100%; padding: 15px; border-radius: 10px; border: 1px solid #1e293b; background: #020617; color: white; font-size: 1.2rem; text-align: center; margin-bottom: 20px; }
        @media(max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <?php if (!$isAuthenticated): ?>
        <div class="card login-card">
            <h2 style="margin-bottom: 15px;">🔒 Akses Terbatas</h2>
            <p style="color: #64748b; margin-bottom: 30px;">Panel ini berisi alat sensitif. Masukkan sandi sistem.</p>
            <form method="POST">
                <?php if($error): ?><div style="color:#ef4444; margin-bottom:15px;"><?= $error ?></div><?php endif; ?>
                <input type="password" name="password" placeholder="Passcode" required autofocus>
                <button type="submit" class="btn btn-blue">Masuk ke Panel</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="head">
                <h1>🛠️ Ultimate Setup Dashboard</h1>
                <div style="display:flex; gap:20px; font-size:0.85rem">
                    <a href="?logout=1" style="color:#ef4444; text-decoration:none">Logout</a>
                    <span style="color:#334155">|</span>
                    <a href="/" style="color:#64748b; text-decoration:none">Home ↗</a>
                </div>
            </div>
            <div class="content">
                <div class="grid">
                    <div class="box">
                        <h3>Status Sistem & Koneksi</h3>
                        <div class="item"><span>Laravel PHP</span> <strong><?= getPhpVersion() ?></strong></div>
                        <div class="item"><span>Basis Data</span> <strong><?= testDbConnection() ?></strong></div>
                        <div class="item"><span>Storage Symlink</span> <strong><?= checkSymlink() ?></strong></div>
                        <div class="item"><span>Folder storage/</span> <strong><?= checkDirPerm('storage') ? '✅ Yes' : '❌ No' ?></strong></div>
                        <div class="item"><span>Folder cache/</span> <strong><?= checkDirPerm('bootstrap/cache') ? '✅ Yes' : '❌ No' ?></strong></div>
                    </div>
                    <div class="box">
                        <h3>Informasi Server</h3>
                        <div class="item"><span>Sistem Operasi</span> <strong>Linux (VPS)</strong></div>
                        <div class="item"><span>Penyimpanan</span> <strong><?= getDiskSpace() ?></strong></div>
                        <div style="margin-top: 15px; font-size: 0.75rem; color: #64748b; line-height: 1.5;">
                            *Jika penyimpanan penuh, segera bersihkan log atau cache untuk menghindari error 500.
                        </div>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                    <div>
                        <h3>Eksekusi Perintah</h3>
                        <a href="?action=deploy" class="btn btn-blue" style="background:#4f46e5;box-shadow: 0 4px 15px #4f46e530;" onclick="return confirm('Mulai proses tarik update otomatis dari GitHub?')">🛰️ Tarik Update GitHub (CI/CD)</a>
                        <a href="?action=full_setup" class="btn btn-red" onclick="return confirm('Database akan direset. Sangat berisiko di server produksi. Lanjutkan?')">🚀 Reset & Setup Database</a>
                        <a href="?action=clear_cache" class="btn btn-gray">🧹 Bersihkan Semua Cache</a>
                        <a href="?action=fix_perms" class="btn btn-gray">🔑 Perbaiki Izin Folder</a>
                    </div>
                    <div>
                        <h3>Pemantauan Log</h3>
                        <a href="?action=view_logs" class="btn btn-gray" style="border: 1px dashed #38bdf840;">🔍 Intip Log Laravel (Laravel.log)</a>
                        <p style="font-size:0.75rem; color:#64748b; padding-top:10px;">Gunakan Fitur Log jika Gambar tidak muncul atau Error 500.</p>
                    </div>
                </div>

                <?php if ($output): ?>
                <div class="term"><?= htmlspecialchars($output) ?></div>
                <?php endif; ?>

                <a href="?action=self_destruct" class="btn btn-red" onclick="return confirm('PERINGATAN! File ini akan dihapus permanen. Gunakan hanya jika setup selesai.')">🗑️ Hapus Panel Setup (Keamanan)</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>