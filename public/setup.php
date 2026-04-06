<?php
/**
 * Ultimate Secured Laravel VPS Setup & Health Dashboard
 * Panti Asuhan Kasih Agape
 * Fixed by Gemini: Light Mode, Dual Column, RAM Info
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
function checkSymlink() { return is_link('storage'); }

function getDiskInfo() {
    $total = disk_total_space("/") / 1024 / 1024 / 1024;
    $free = disk_free_space("/") / 1024 / 1024 / 1024;
    $used = $total - $free;
    $percent = ($used / $total) * 100;

    return [
        'total' => round($total, 1),
        'free' => round($free, 1),
        'used' => round($used, 1),
        'percent' => round($percent, 1)
    ];
}

function getRamInfo() {
    if (!is_readable('/proc/meminfo')) return null;
    $data = file_get_contents('/proc/meminfo');
    preg_match('/MemTotal:\s+(\d+)/', $data, $totalMatch);
    preg_match('/MemAvailable:\s+(\d+)/', $data, $availMatch); // Lebih akurat dari MemFree di Linux
    
    $total = isset($totalMatch[1]) ? (int)$totalMatch[1] / 1024 / 1024 : 0;
    $avail = isset($availMatch[1]) ? (int)$availMatch[1] / 1024 / 1024 : 0;
    
    if ($total > 0) {
        $used = $total - $avail;
        $percent = ($used / $total) * 100;
        return [
            'total' => round($total, 2),
            'free' => round($avail, 2),
            'used' => round($used, 2),
            'percent' => round($percent, 1)
        ];
    }
    return null;
}

function testDbConnection() {
    try {
        if (!file_exists('../.env')) return false;
        $env = file_get_contents('../.env');
        preg_match('/DB_HOST=(.*)/', $env, $host);
        preg_match('/DB_DATABASE=(.*)/', $env, $db);
        preg_match('/DB_USERNAME=(.*)/', $env, $user);
        preg_match('/DB_PASSWORD=(.*)/', $env, $pass);
        
        $mysqli = @new mysqli(
            trim($host[1] ?? '127.0.0.1'), 
            trim($user[1] ?? ''), 
            trim($pass[1] ?? ''), 
            trim($db[1] ?? '')
        );
        
        return !$mysqli->connect_error;
    } catch (Exception $e) { return false; }
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
        $output .= "🚀 CI/CD Deployment GitHub...\n" . str_repeat("-", 40) . "\n";
        $output .= "🏃 [1/4] git pull...\n";
        $output .= shell_exec("cd .. && git pull origin main 2>&1") . "\n";
        $output .= "🏃 [2/4] composer install...\n";
        $output .= shell_exec("cd .. && composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1") . "\n";
        $output .= "🏃 [3/4] optimize:clear...\n";
        $output .= shell_exec("cd .. && php artisan optimize:clear 2>&1") . "\n";
        $output .= "🏃 [4/4] migrate...\n";
        $output .= shell_exec("cd .. && php artisan migrate --force 2>&1") . "\n";
        $output .= "\n✅ Sistem diupdate & siap digunakan!\n";
    } elseif (isset($commands[$action])) {
        if ($action == 'full_setup' && file_exists('index.html')) unlink('index.html');
        foreach ($commands[$action] as $cmd) {
            $output .= "🏃 php artisan $cmd...\n";
            $output .= shell_exec("cd .. && php artisan $cmd 2>&1") . "\n";
        }
    }
}

$disk = getDiskInfo();
$ram = getRamInfo();
$dbConnected = testDbConnection();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Control | Agape</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f3f4f6; --card: #ffffff; --border: #e5e7eb; --text: #1f2937; --muted: #6b7280;
            --primary: #4f46e5; --primary-hover: #4338ca; 
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        
        .container { width: 100%; max-width: 1050px; }
        
        .card { background: var(--card); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1); overflow: hidden; }
        
        .header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f9fafb; }
        .header h1 { font-size: 1.15rem; font-weight: 600; letter-spacing: -0.02em; display: flex; align-items: center; gap: 8px; color: #111827; }
        .header-links a { color: var(--muted); text-decoration: none; font-size: 0.85rem; margin-left: 16px; transition: color 0.2s; font-weight: 500; }
        .header-links a:hover { color: var(--text); }
        .header-links .logout { color: var(--danger); opacity: 0.9; }
        
        .two-columns { display: grid; grid-template-columns: 1.1fr 0.9fr; }
        .col-left { padding: 24px; border-right: 1px solid var(--border); }
        .col-right { padding: 24px; background: #f9fafb; display: flex; flex-direction: column; }
        
        @media(max-width: 800px) {
            .two-columns { grid-template-columns: 1fr; }
            .col-left { border-right: none; border-bottom: 1px solid var(--border); }
        }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .stat-box { background: #fff; border: 1px solid var(--border); padding: 14px; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .stat-label { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; font-weight: 600; }
        .stat-value { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px; color: #111827; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-green { background: var(--success); box-shadow: 0 0 6px rgba(16,185,129,0.4); }
        .dot-red { background: var(--danger); box-shadow: 0 0 6px rgba(239,68,68,0.4); }

        /* Hardware info section */
        .hw-section { margin-bottom: 24px; background: #fff; padding: 16px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .hw-header { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px; font-weight: 600; color: #374151; }
        .hw-subtext { font-size: 0.75rem; color: var(--muted); font-weight: 500; }
        .progress-bg { width: 100%; height: 8px; background: #f3f4f6; border-radius: 8px; overflow: hidden; margin-top: 6px; }
        .progress-bar { height: 100%; border-radius: 8px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Actions Grid */
        .actions-label { font-size: 0.85rem; font-weight: 600; color: #111827; margin-bottom: 12px; }
        .btn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
        .btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 500; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s ease; }
        
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2); border: 1px solid #4338ca; }
        .btn-primary:hover { background: var(--primary-hover); }
        
        .btn-secondary { background: #fff; color: #374151; border-color: #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
        .btn-secondary:hover { background: #f9fafb; border-color: #9ca3af; }

        .btn-danger { background: #fef2f2; color: var(--danger); border-color: #fecaca; }
        .btn-danger:hover { background: #fee2e2; border-color: #fca5a5; }

        /* Terminal Column */
        .term-title { font-size: 0.85rem; font-weight: 600; color: #1f2937; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
        .terminal { background: #0f172a; color: #38bdf8; padding: 16px; border-radius: 10px; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; line-height: 1.5; height: 100%; min-height: 250px; overflow-y: auto; white-space: pre-wrap; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }
        .term-empty { color: #475569; display: flex; align-items: center; justify-content: center; height: 100%; font-style: italic; }

        /* Login */
        .login-card { max-width: 400px; padding: 40px 30px; margin: 0 auto; }
        .login-card h2 { margin-bottom: 8px; font-weight: 600; font-size: 1.5rem; text-align: center; color: #111827;}
        .login-card p { color: var(--muted); font-size: 0.9rem; margin-bottom: 24px; text-align: center; }
        input[type="password"] { width: 100%; padding: 14px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #111827; font-size: 1rem; text-align: center; margin-bottom: 16px; outline: none; transition: border-color 0.2s; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02); }
        input[type="password"]:focus { border-color: var(--primary); ring: 2px solid var(--primary); }
    </style>
</head>
<body>

<div class="container">
    <?php if (!$isAuthenticated): ?>
        <div class="card login-card">
            <h2>🔒 Akses Sistem</h2>
            <p>Masukkan sandi rahasia administrator.</p>
            <form method="POST">
                <?php if($error): ?><div style="color:var(--danger); margin-bottom:16px; font-size: 0.9rem; text-align: center; font-weight: 500;"><?= $error ?></div><?php endif; ?>
                <input type="password" name="password" placeholder="Passcode" required autofocus>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Masuk Log Server</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="header">
                <h1>⚙️ Server Control Panel</h1>
                <div class="header-links">
                    <a href="/">Web Home ↗</a>
                    <a href="?logout=1" class="logout">Logout</a>
                </div>
            </div>
            
            <div class="two-columns">
                <!-- Kiri: Kontrol & Status -->
                <div class="col-left">
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-label">PHP Version</div>
                            <div class="stat-value"><?= getPhpVersion() ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Database</div>
                            <div class="stat-value">
                                <span class="status-dot <?= $dbConnected ? 'dot-green' : 'dot-red' ?>"></span>
                                <?= $dbConnected ? 'Terhubung' : 'Terputus' ?>
                            </div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Symlink</div>
                            <div class="stat-value">
                                <span class="status-dot <?= checkSymlink() ? 'dot-green' : 'dot-red' ?>"></span>
                                <?= checkSymlink() ? 'Aktif' : 'Error' ?>
                            </div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Permission</div>
                            <div class="stat-value">
                                <span class="status-dot <?= checkDirPerm('storage') && checkDirPerm('bootstrap/cache') ? 'dot-green' : 'dot-red' ?>"></span>
                                <?= checkDirPerm('storage') && checkDirPerm('bootstrap/cache') ? 'Aman' : 'Fix Needed' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hardware Info -->
                    <div class="hw-section">
                        <!-- RAM -->
                        <?php if($ram): ?>
                        <div class="hw-header">
                            <span>Memori Server (RAM) <span class="hw-subtext">&bull; <?= $ram['percent'] ?>% Terpakai</span></span>
                            <span style="color: <?= $ram['percent'] > 85 ? 'var(--danger)' : 'var(--primary)' ?>;"><?= $ram['free'] ?> GB Sisa</span>
                        </div>
                        <div class="progress-bg" style="margin-bottom: 18px;">
                            <div class="progress-bar" style="width: <?= $ram['percent'] ?>%; background: <?= $ram['percent'] > 85 ? 'var(--danger)' : ($ram['percent'] > 70 ? 'var(--warning)' : 'var(--primary)') ?>;"></div>
                        </div>
                        <?php endif; ?>

                        <!-- Storage -->
                        <div class="hw-header">
                            <span>Disk Storage <span class="hw-subtext">&bull; <?= $disk['percent'] ?>% Terpakai</span></span>
                            <span style="color: <?= $disk['percent'] > 85 ? 'var(--danger)' : 'var(--success)' ?>;"><?= $disk['free'] ?> GB Sisa</span>
                        </div>
                        <div class="progress-bg">
                            <div class="progress-bar" style="width: <?= $disk['percent'] ?>%; background: <?= $disk['percent'] > 85 ? 'var(--danger)' : ($disk['percent'] > 70 ? 'var(--warning)' : 'var(--success)') ?>;"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="actions-label">Otomasi & Tindakan Khusus</div>
                    
                    <a href="?action=deploy" class="btn btn-primary" style="margin-bottom: 10px;" onclick="return confirm('Mulai tarik update otomatis dari GitHub?')">
                        🚀 Tarik Update dari GitHub (CI/CD)
                    </a>
                    
                    <div class="btn-grid">
                        <a href="?action=view_logs" class="btn btn-secondary">📄 Intip Log Error</a>
                        <a href="?action=clear_cache" class="btn btn-secondary">🧹 Bersihan Cache</a>
                        <a href="?action=fix_perms" class="btn btn-secondary">🔑 Fix Folder Izin</a>
                        <a href="?action=full_setup" class="btn btn-danger" onclick="return confirm('Hanya untuk instalasi baru! Yakin reset Database?')">⚠️ Reset Database</a>
                    </div>
                    
                </div>
                
                <!-- Kanan: Terminal Output -->
                <div class="col-right">
                    <div class="term-title">
                        💻 Log Eksekusi Terminal
                    </div>
                    <div class="terminal">
                        <?php if ($output): ?>
                            <?= htmlspecialchars($output) ?>
                        <?php else: ?>
                            <div class="term-empty">Menunggu perintah sistem...</div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="?action=self_destruct" class="btn btn-danger" style="display:inline-flex; width:auto; font-size: 0.75rem; padding: 6px 12px; background: transparent; border-color: transparent; border-bottom: 1px dotted var(--danger); border-radius:0; opacity:0.8;" onclick="return confirm('Panel akan dihapus permanen! Yakin?')">
                            Hapus Panel (Keamanan)
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>