<?php
session_start();
// A. Tambahkan Header No-Cache untuk mengatasi Nginx Buffering (Bypass CloudPanel Cache)
header('X-Accel-Buffering: no');

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
function getPhpVersion()
{
    return phpversion();
}
function checkDirPerm($path)
{
    return is_writable('../' . $path);
}
function checkSymlink()
{
    return is_link('storage');
}

function getDiskInfo()
{
    $total = @disk_total_space("/") / 1024 / 1024 / 1024;
    $free = @disk_free_space("/") / 1024 / 1024 / 1024;
    $used = $total - $free;
    $percent = ($total > 0) ? ($used / $total) * 100 : 0;

    return [
        'total' => round($total, 1),
        'free' => round($free, 1),
        'used' => round($used, 1),
        'percent' => round($percent, 1)
    ];
}

function getRamInfo()
{
    if (!is_readable('/proc/meminfo'))
        return null;
    $data = file_get_contents('/proc/meminfo');
    preg_match('/MemTotal:\s+(\d+)/', $data, $totalMatch);
    preg_match('/MemAvailable:\s+(\d+)/', $data, $availMatch);

    $total = isset($totalMatch[1]) ? (int) $totalMatch[1] / 1024 / 1024 : 0;
    $avail = isset($availMatch[1]) ? (int) $availMatch[1] / 1024 / 1024 : 0;

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

function testDbConnection()
{
    if (!file_exists('../.env'))
        return false;

    try {
        $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $config = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0)
                continue;
            $parts = explode('=', $line, 2);
            if (count($parts) == 2) {
                $config[trim($parts[0])] = trim($parts[1], " \t\n\r\0\x0B\"'");
            }
        }

        $mysqli = @new mysqli(
            $config['DB_HOST'] ?? '127.0.0.1',
            $config['DB_USERNAME'] ?? '',
            $config['DB_PASSWORD'] ?? '',
            $config['DB_DATABASE'] ?? '',
            $config['DB_PORT'] ?? 3306
        );

        return !$mysqli->connect_error;
    } catch (Exception $e) {
        return false;
    }
}

$action = $_GET['action'] ?? null;
$output = "";

if ($isAuthenticated) {
    set_time_limit(300); // 5 Menit Limit

    // Custom Terminal Input Logic
    if (isset($_POST['custom_cmd']) && trim($_POST['custom_cmd']) !== '') {
        $cmd = trim($_POST['custom_cmd']);
        $output .= "🚀 Menjalankan perintah Manual:\n";
        $output .= "$ " . htmlspecialchars($cmd) . "\n" . str_repeat("-", 40) . "\n";
        $output .= htmlspecialchars(shell_exec("cd .. && " . $cmd . " 2>&1")) . "\n";
    }

    if ($action == 'self_destruct') {
        unlink(__FILE__);
        header("Location: /");
        exit;
    }

    if ($action == 'fix_perms') {
        $output .= "🔧 Memperbaiki Izin Folder...\n";
        $output .= shell_exec("cd .. && chmod -R 775 storage bootstrap/cache 2>&1");
        $output .= "✅ Selesai.\n";
    } elseif ($action == 'view_logs') {
        $logPath = '../storage/logs/laravel.log';
        if (file_exists($logPath)) {
            $output .= "📄 50 Baris Terakhir Log Laravel:\n" . str_repeat("-", 40) . "\n";
            $output .= htmlspecialchars(shell_exec("tail -n 50 " . escapeshellarg($logPath)));
        } else {
            $output .= "❌ File log tidak ditemukan.";
        }
    } elseif ($action == 'deploy') {
        $output .= "🚀 CI/CD Deployment GitHub...\n" . str_repeat("-", 40) . "\n";
        $output .= "🏃 [1/4] git pull...\n";
        $output .= htmlspecialchars(shell_exec("cd .. && git pull origin main 2>&1")) . "\n";
        $output .= "🏃 [2/4] composer install...\n";
        $output .= htmlspecialchars(shell_exec("cd .. && export COMPOSER_ALLOW_SUPERUSER=1 && composer install --no-interaction --no-ansi --no-dev --prefer-dist --optimize-autoloader 2>&1")) . "\n";
        $output .= "🏃 [3/4] php artisan optimize:clear...\n";
        $output .= htmlspecialchars(shell_exec("cd .. && php artisan optimize:clear --no-ansi 2>&1")) . "\n";
        $output .= "🏃 [4/4] php artisan migrate --force...\n";
        $output .= htmlspecialchars(shell_exec("cd .. && php artisan migrate --force --no-ansi 2>&1")) . "\n";
        $output .= "\n✅ Deployment Selesai!\n";
    } elseif ($action == 'clear_cache') {
        $cmds = ['optimize:clear', 'cache:clear', 'config:clear', 'view:clear'];
        foreach ($cmds as $cmd) {
            $output .= "🏃 php artisan $cmd...\n";
            $output .= htmlspecialchars(shell_exec("cd .. && php artisan $cmd --no-ansi 2>&1")) . "\n";
        }
    } elseif ($action == 'full_setup') {
        if (file_exists('index.html'))
            unlink('index.html');
        $cmds = ['key:generate', 'storage:link', 'migrate:fresh --seed', 'optimize:clear'];
        foreach ($cmds as $cmd) {
            $output .= "🏃 php artisan $cmd...\n";
            $output .= htmlspecialchars(shell_exec("cd .. && php artisan $cmd --no-ansi 2>&1")) . "\n";
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
            --terminal: #0f172a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }

        .card {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

        .header h1 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
        }

        .layout {
            display: grid;
            grid-template-columns: 350px 1fr;
        }

        .sidebar {
            padding: 24px;
            border-right: 1px solid var(--border);
        }

        .main {
            padding: 24px;
            background: #fcfcfd;
            display: flex;
            flex-direction: column;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-item {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }

        .stat-label {
            font-size: 0.65rem;
            color: var(--muted);
            text-transform: uppercase;
            font-weight: 700;
        }

        .stat-value {
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .progress-group {
            margin-bottom: 15px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .progress-bar-bg {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            transition: 0.5s;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: 0.2s;
            margin-bottom: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-outline {
            border-color: var(--border);
            color: var(--text);
            background: #fff;
        }

        .btn-outline:hover {
            background: #f8fafc;
        }

        .btn-danger-lite {
            color: var(--danger);
            font-size: 0.7rem;
            border-bottom: 1px dotted var(--danger);
            width: fit-content;
            margin: 20px auto 0;
        }

        .terminal {
            background: var(--terminal);
            color: #7dd3fc;
            padding: 20px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            flex: 1;
            min-height: 400px;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .terminal-input-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .terminal-input {
            flex: 1;
            padding: 12px 15px;
            border-radius: 6px;
            border: 1px solid var(--terminal);
            background: #1e293b;
            color: #7dd3fc;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            outline: none;
            transition: 0.2s;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .terminal-input:focus {
            border-color: var(--primary);
            background: var(--terminal);
        }

        .terminal-input::placeholder {
            color: #475569;
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .bg-success {
            background: var(--success);
        }

        .bg-danger {
            background: var(--danger);
        }

        @media (max-width: 850px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }
        }

        .login-box {
            max-width: 350px;
            margin: 0 auto;
            padding: 30px;
            text-align: center;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            margin-bottom: 15px;
            text-align: center;
            outline: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <?php if (!$isAuthenticated): ?>
            <div class="card login-box">
                <h2 style="margin-bottom: 10px;">🔒 Protected</h2>
                <p style="font-size: 0.8rem; color: var(--muted); margin-bottom: 20px;">Masukkan akses kode Agape</p>
                <form method="POST">
                    <?php if ($error): ?>
                        <p style="color:var(--danger); font-size:0.75rem; margin-bottom:10px;"><?= $error ?></p><?php endif; ?>
                    <input type="password" name="password" placeholder="Passcode" required>
                    <button type="submit" class="btn btn-primary">Login Dashboard</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="header">
                    <h1>SERVER CONTROL PANEL</h1>
                    <div style="font-size: 0.75rem; font-weight: 600;">
                        <a href="/" style="text-decoration:none; color: var(--muted);">Home ↗</a>
                        <a href="?logout=1" style="margin-left:15px; color: var(--danger); text-decoration:none;">Logout</a>
                    </div>
                </div>

                <div class="layout">
                    <div class="sidebar">
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
                                    <?= checkDirPerm('storage') ? 'Safe' : 'Fix' ?>
                                </div>
                            </div>
                        </div>

                        <div class="progress-group">
                            <div class="progress-label">
                                <span>RAM Usage</span>
                                <span><?= $ram['percent'] ?? 0 ?>%</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill"
                                    style="width:<?= $ram['percent'] ?? 0 ?>%; background:<?= ($ram['percent'] ?? 0) > 80 ? 'var(--danger)' : 'var(--primary)' ?>;">
                                </div>
                            </div>
                        </div>

                        <div class="progress-group" style="margin-bottom: 30px;">
                            <div class="progress-label">
                                <span>Disk Storage</span>
                                <span><?= $disk['percent'] ?>%</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill"
                                    style="width:<?= $disk['percent'] ?>%; background:<?= $disk['percent'] > 80 ? 'var(--danger)' : 'var(--success)' ?>;">
                                </div>
                            </div>
                        </div>

                        <p
                            style="font-size: 0.7rem; font-weight: 700; color: var(--muted); margin-bottom: 10px; text-transform: uppercase;">
                            Command Actions</p>
                        <a href="?action=deploy" class="btn btn-primary"
                            onclick="return confirm('Jalankan Deploy CI/CD?')">🚀 CI/CD GitHub Update</a>
                        <a href="?action=clear_cache" class="btn btn-outline">🧹 Clear App Cache</a>
                        <a href="?action=view_logs" class="btn btn-outline">📄 View Error Logs</a>
                        <a href="?action=fix_perms" class="btn btn-outline">🔑 Fix Folder Permissions</a>

                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                            <a href="?action=full_setup" class="btn btn-outline"
                                style="color: var(--danger); border-color: #fee2e2;"
                                onclick="return confirm('Database akan direset! Yakin?')">⚠️ Reset Database</a>
                        </div>
                    </div>

                    <div class="main">
                        <div
                            style="font-size: 0.7rem; font-weight: 700; color: var(--muted); margin-bottom: 10px; text-transform: uppercase;">
                            Terminal Dashboard</div>
                        <div class="terminal"><?= $output ?: "System Ready. Menunggu perintah..." ?></div>

                        <form method="POST" class="terminal-input-group">
                            <input type="text" name="custom_cmd" class="terminal-input"
                                placeholder="Ketik perintah (ex: php artisan route:list) lalu tekan Enter..."
                                autocomplete="off">
                            <button type="submit" class="btn btn-primary"
                                style="width: auto; padding: 0 20px; margin: 0;">Jalankan</button>
                        </form>

                        <a href="?action=self_destruct" class="btn btn-danger-lite"
                            onclick="return confirm('Hapus file setup ini?')">Hapus Panel Setup Secara Permanen</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>