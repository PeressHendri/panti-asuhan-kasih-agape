@extends('layouts.app')
@section('title', 'Absen Wajah (Webcam)')
@section('header-title', 'Absen Wajah (Webcam)')

@push('styles')
<style>
:root {
    --fr-bg: #0f172a;
    --fr-card: #1e293b;
    --fr-border: #334155;
    --fr-accent: #3b82f6;
    --fr-green: #22c55e;
    --fr-yellow: #f59e0b;
    --fr-red: #ef4444;
}

.fr-wrapper { display: grid; grid-template-columns: 1fr 420px; gap: 20px; }
@media(max-width:992px){ .fr-wrapper { grid-template-columns: 1fr; } }

/* Camera Card */
.cam-card {
    background: var(--fr-bg);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--fr-border);
    box-shadow: 0 25px 60px rgba(0,0,0,.5);
}
.cam-header {
    background: linear-gradient(135deg,#1e3a5f,#1e3c72);
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
}
.cam-header h5 { color:#fff; margin:0; font-weight:800; font-size:.95rem; }
.live-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(239,68,68,.15); border:1px solid rgba(239,68,68,.4);
    color:#ef4444; padding:4px 12px; border-radius:50px; font-size:.7rem; font-weight:800;
}
.live-dot { width:7px;height:7px;border-radius:50%;background:#ef4444;animation:pulseDot 1.4s infinite; }
@keyframes pulseDot{0%{box-shadow:0 0 0 0 rgba(239,68,68,.6)}70%{box-shadow:0 0 0 8px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}

.cam-body { position:relative; background:#000; min-height:420px; display:flex; align-items:center; justify-content:center; }
#webcam { width:100%; max-height:420px; object-fit:cover; transform:scaleX(-1); display:block; }

/* Face guide */
.face-guide {
    position:absolute; width:220px; height:280px;
    border:2px dashed rgba(255,255,255,.3); border-radius:50%/40%;
    pointer-events:none; box-shadow:0 0 0 9999px rgba(0,0,0,.4); z-index:5;
    transition: border-color .3s, box-shadow .3s;
}
.face-guide.detecting { border-color:#3b82f6; box-shadow:0 0 0 9999px rgba(0,0,0,.4), 0 0 20px rgba(59,130,246,.5); }
.face-guide.success   { border-color:#22c55e; box-shadow:0 0 0 9999px rgba(0,0,0,.4), 0 0 20px rgba(34,197,94,.5); }
.face-guide.error     { border-color:#ef4444; box-shadow:0 0 0 9999px rgba(0,0,0,.4), 0 0 20px rgba(239,68,68,.4); }

.corner { position:absolute; width:18px; height:18px; border:3px solid #fbbf24; pointer-events:none; }
.corner.tl{top:-3px;left:-3px;border-right:0;border-bottom:0;border-top-left-radius:10px;}
.corner.tr{top:-3px;right:-3px;border-left:0;border-bottom:0;border-top-right-radius:10px;}
.corner.bl{bottom:-3px;left:-3px;border-right:0;border-top:0;border-bottom-left-radius:10px;}
.corner.br{bottom:-3px;right:-3px;border-left:0;border-top:0;border-bottom-right-radius:10px;}

/* Scan line */
.scan-line {
    position:absolute; left:0; width:100%; height:3px;
    background:linear-gradient(transparent,rgba(59,130,246,.9),transparent);
    box-shadow:0 0 12px rgba(59,130,246,.8);
    z-index:6; pointer-events:none; display:none;
    animation:scanMove 2s linear infinite;
}
@keyframes scanMove{0%{top:0}50%{top:100%}100%{top:0}}

/* Status bar */
.cam-status {
    background:#0f172a; padding:10px 20px;
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.status-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 14px; border-radius:50px; font-size:.75rem; font-weight:700; border:1px solid;
}
.status-badge.scanning { background:rgba(59,130,246,.1); border-color:rgba(59,130,246,.4); color:#93c5fd; }
.status-badge.idle     { background:rgba(100,116,139,.1); border-color:rgba(100,116,139,.3); color:#94a3b8; }
.status-badge.success  { background:rgba(34,197,94,.1);  border-color:rgba(34,197,94,.4);  color:#86efac; }
.status-badge.error    { background:rgba(239,68,68,.1);   border-color:rgba(239,68,68,.3);   color:#fca5a5; }

.mode-toggle { display:flex; gap:6px; }
.mode-btn {
    padding:5px 14px; border-radius:50px; border:1.5px solid;
    font-size:.75rem; font-weight:700; cursor:pointer; transition:all .2s;
}
.mode-btn.checkin  { border-color:#22c55e; color:#22c55e; background:transparent; }
.mode-btn.checkin.active  { background:#22c55e; color:#fff; }
.mode-btn.checkout { border-color:#f59e0b; color:#f59e0b; background:transparent; }
.mode-btn.checkout.active { background:#f59e0b; color:#000; }

/* Cooldown bar */
.cooldown-bar {
    height:3px; background:#334155; margin:0; overflow:hidden;
}
.cooldown-fill { height:100%; background:var(--fr-accent); width:0; transition:width .1s linear; }

/* Result Panel */
.result-panel {
    background: var(--fr-card);
    border-radius:20px;
    border:1px solid var(--fr-border);
    overflow:hidden;
    display:flex; flex-direction:column;
}
.result-header {
    padding:14px 20px;
    border-bottom:1px solid var(--fr-border);
    display:flex; align-items:center; justify-content:space-between;
}
.result-header h6 { color:#e2e8f0; margin:0; font-weight:800; font-size:.9rem; }

/* Last result box */
.last-result {
    padding:20px; border-bottom:1px solid var(--fr-border);
    min-height:200px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
}
.result-avatar {
    width:80px; height:80px; border-radius:50%; object-fit:cover;
    border:3px solid var(--fr-green); box-shadow:0 0 20px rgba(34,197,94,.3);
    margin-bottom:12px;
}
.result-name { font-size:1.1rem; font-weight:800; color:#f1f5f9; margin-bottom:4px; }
.result-meta { font-size:.78rem; color:#94a3b8; margin-bottom:8px; }
.result-badge {
    display:inline-block; padding:3px 14px; border-radius:50px; font-size:.72rem; font-weight:800;
}
.result-badge.ok  { background:rgba(34,197,94,.15); color:#22c55e; border:1px solid rgba(34,197,94,.4); }
.result-badge.err { background:rgba(239,68,68,.15);  color:#ef4444; border:1px solid rgba(239,68,68,.4); }
.result-badge.wait{ background:rgba(100,116,139,.15); color:#94a3b8; border:1px solid rgba(100,116,139,.3); }

/* Attendance list */
.att-list { flex:1; overflow-y:auto; }
.att-list::-webkit-scrollbar{ width:4px; }
.att-list::-webkit-scrollbar-thumb{ background:#334155; border-radius:4px; }
.att-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 16px; border-bottom:1px solid rgba(51,65,85,.5);
    transition:background .15s;
}
.att-item:hover{ background:rgba(59,130,246,.05); }
.att-avatar {
    width:36px; height:36px; border-radius:10px; flex-shrink:0;
    background:linear-gradient(135deg,#3b82f6,#8b5cf6);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.85rem;
}
.att-name { font-size:.83rem; font-weight:700; color:#e2e8f0; }
.att-time { font-size:.72rem; color:#64748b; font-family:monospace; }
.att-chip {
    margin-left:auto; padding:2px 10px; border-radius:50px; font-size:.65rem; font-weight:800;
}
.att-chip.in  { background:rgba(34,197,94,.15);  color:#22c55e; }
.att-chip.out { background:rgba(245,158,11,.15); color:#f59e0b; }

/* Camera overlay */
#cam-overlay {
    position:absolute; inset:0; background:rgba(15,23,42,.85);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    z-index:10; color:#fff; text-align:center;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
<div class="fr-wrapper">

    {{-- ── KAMERA ──────────────────────────────────────────────── --}}
    <div class="cam-card">
        <div class="cam-header">
            <h5><i class="fas fa-camera me-2 text-yellow-300" style="color:#fbbf24;"></i>Pemindai Kamera Live</h5>
            <span class="live-pill"><span class="live-dot"></span>LIVE</span>
        </div>

        <div class="cam-body">
            <video id="webcam" autoplay playsinline></video>
            <div class="scan-line" id="scan-line"></div>
            <div class="face-guide" id="face-guide">
                <div class="corner tl"></div><div class="corner tr"></div>
                <div class="corner bl"></div><div class="corner br"></div>
            </div>
            <div id="cam-overlay">
                <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                <h5 class="fw-bold mb-1">Menghubungkan Kamera...</h5>
                <p class="text-white-50 small px-4">Izinkan akses webcam di browser Anda.</p>
                <button id="btn-retry" class="btn btn-outline-light btn-sm rounded-pill px-4 mt-1 d-none">
                    <i class="fas fa-sync me-1"></i>Coba Lagi
                </button>
            </div>
        </div>

        <div class="cooldown-bar"><div class="cooldown-fill" id="cooldown-fill"></div></div>

        <div class="cam-status">
            <span class="status-badge idle" id="status-badge">
                <i class="fas fa-circle-notch fa-spin d-none" id="spin-icon"></i>
                <i class="fas fa-eye" id="eye-icon"></i>
                <span id="status-text">Memulai kamera...</span>
            </span>
            <div class="mode-toggle">
                <button class="mode-btn checkin active" id="btn-checkin" onclick="setMode('check_in')">
                    <i class="fas fa-sign-in-alt me-1"></i>Check In
                </button>
                <button class="mode-btn checkout" id="btn-checkout" onclick="setMode('check_out')">
                    <i class="fas fa-sign-out-alt me-1"></i>Check Out
                </button>
            </div>
        </div>
    </div>

    {{-- ── PANEL HASIL + KEHADIRAN ─────────────────────────────── --}}
    <div class="result-panel">
        <div class="result-header">
            <h6><i class="fas fa-id-card me-2 text-primary"></i>Hasil & Kehadiran Hari Ini</h6>
            <span id="att-count" class="badge" style="background:rgba(59,130,246,.15);color:#60a5fa;font-size:.7rem;"></span>
        </div>

        {{-- Hasil scan terakhir --}}
        <div class="last-result" id="last-result">
            <i class="fas fa-user-check fa-3x mb-3" style="color:#334155;"></i>
            <div class="result-badge wait">MENUNGGU SCAN</div>
            <p class="text-muted small mt-2 mb-0">Arahkan wajah ke kamera.<br>Deteksi otomatis setiap beberapa detik.</p>
        </div>

        {{-- Daftar kehadiran hari ini --}}
        <div style="padding:10px 16px 6px;border-bottom:1px solid #1e293b;">
            <span style="font-size:.7rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.6px;">
                Kehadiran Hari Ini
            </span>
        </div>
        <div class="att-list" id="att-list">
            @forelse($todayAttendances as $att)
            <div class="att-item">
                <div class="att-avatar">{{ strtoupper(substr($att->child->nama ?? 'A', 0, 1)) }}</div>
                <div>
                    <div class="att-name">{{ $att->child->nama ?? '-' }}</div>
                    <div class="att-time">
                        @if($att->check_in) IN {{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }} @endif
                        @if($att->check_out) · OUT {{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }} @endif
                    </div>
                </div>
                @if($att->check_out)
                    <span class="att-chip out">KELUAR</span>
                @else
                    <span class="att-chip in">HADIR</span>
                @endif
            </div>
            @empty
            <div id="att-empty" style="padding:30px;text-align:center;color:#475569;">
                <i class="fas fa-calendar-day fa-2x mb-2 d-block opacity-50"></i>
                <div style="font-size:.82rem;">Belum ada kehadiran hari ini</div>
            </div>
            @endforelse
        </div>
    </div>

</div>
</div>
<canvas id="snap" class="d-none" width="640" height="480"></canvas>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ─── State ──────────────────────────────────────────────────────
    const SCAN_INTERVAL   = 4000;  // ms antar scan otomatis
    const COOLDOWN_MS     = 8000;  // ms cooldown setelah berhasil dikenali
    const SCAN_URL        = '{{ route("dashboard.face-recognition.web-scan") }}';
    const ATT_URL         = '{{ route("dashboard.face-recognition.recent-attendance") }}';
    const CSRF            = '{{ csrf_token() }}';

    let mode        = 'check_in';
    let scanning    = false;
    let inCooldown  = false;
    let cameraReady = false;
    let scanTimer   = null;
    let cooldownTimer = null;

    // ─── DOM ────────────────────────────────────────────────────────
    const video       = document.getElementById('webcam');
    const overlay     = document.getElementById('cam-overlay');
    const retryBtn    = document.getElementById('btn-retry');
    const scanLine    = document.getElementById('scan-line');
    const faceGuide   = document.getElementById('face-guide');
    const statusBadge = document.getElementById('status-badge');
    const statusText  = document.getElementById('status-text');
    const spinIcon    = document.getElementById('spin-icon');
    const eyeIcon     = document.getElementById('eye-icon');
    const cooldownFill= document.getElementById('cooldown-fill');
    const lastResult  = document.getElementById('last-result');
    const attList     = document.getElementById('att-list');
    const attCount    = document.getElementById('att-count');
    const canvas      = document.getElementById('snap');
    const ctx         = canvas.getContext('2d');

    // ─── Mode toggle ────────────────────────────────────────────────
    window.setMode = function(m) {
        mode = m;
        document.getElementById('btn-checkin').classList.toggle('active', m === 'check_in');
        document.getElementById('btn-checkout').classList.toggle('active', m === 'check_out');
    };

    // ─── Camera init ────────────────────────────────────────────────
    function initCamera() {
        navigator.mediaDevices.getUserMedia({ video: { width:640, height:480, facingMode:'user' } })
        .then(stream => {
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
                overlay.style.display = 'none';
                cameraReady = true;
                setStatus('idle', 'Siap — Mendeteksi wajah...');
                startAutoScan();
            };
        })
        .catch(() => {
            overlay.querySelector('h5').innerText = 'Akses Kamera Ditolak';
            overlay.querySelector('p').innerText = 'Periksa izin kamera di browser.';
            overlay.querySelector('.spinner-border').classList.add('d-none');
            retryBtn.classList.remove('d-none');
        });
    }

    retryBtn.addEventListener('click', () => {
        retryBtn.classList.add('d-none');
        overlay.querySelector('.spinner-border').classList.remove('d-none');
        overlay.querySelector('h5').innerText = 'Menghubungkan Kamera...';
        initCamera();
    });

    // ─── Status helper ──────────────────────────────────────────────
    function setStatus(type, text) {
        statusBadge.className = `status-badge ${type}`;
        statusText.innerText = text;
        spinIcon.classList.toggle('d-none', type !== 'scanning');
        eyeIcon.classList.toggle('d-none', type === 'scanning');
        faceGuide.className = `face-guide ${type === 'scanning' ? 'detecting' : type === 'success' ? 'success' : type === 'error' ? 'error' : ''}`;
    }

    // ─── Auto scan loop ─────────────────────────────────────────────
    function startAutoScan() {
        if (scanTimer) clearInterval(scanTimer);
        scanTimer = setInterval(() => {
            if (!cameraReady || scanning || inCooldown) return;
            doScan();
        }, SCAN_INTERVAL);
    }

    // ─── Take snapshot ──────────────────────────────────────────────
    function captureFrame() {
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        return canvas.toDataURL('image/jpeg', 0.85);
    }

    // ─── Speech ─────────────────────────────────────────────────────
    function speak(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'id-ID'; u.rate = 1.05;
        window.speechSynthesis.speak(u);
    }

    // ─── Show last result ───────────────────────────────────────────
    function showResult(ok, data, msg) {
        if (ok) {
            lastResult.innerHTML = `
                <img src="${data.foto}" class="result-avatar" onerror="this.style.display='none'">
                <div class="result-name">${data.nama}</div>
                <div class="result-meta">Akurasi: <strong style="color:#22c55e;">${parseFloat(data.confidence).toFixed(1)}%</strong> · ${data.waktu}</div>
                <span class="result-badge ok">${data.status}</span>`;
        } else {
            lastResult.innerHTML = `
                <div style="width:70px;height:70px;border-radius:50%;background:rgba(239,68,68,.1);border:2px solid rgba(239,68,68,.3);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-user-times fa-2x" style="color:#ef4444;"></i>
                </div>
                <span class="result-badge err">TIDAK DIKENAL</span>
                <p class="text-muted small mt-2 mb-0">${msg}</p>`;
        }
    }

    // ─── Cooldown bar ───────────────────────────────────────────────
    function startCooldown() {
        inCooldown = true;
        const start = Date.now();
        const tick = () => {
            const pct = Math.min(100, ((Date.now() - start) / COOLDOWN_MS) * 100);
            cooldownFill.style.width = pct + '%';
            if (pct < 100) { cooldownTimer = requestAnimationFrame(tick); }
            else { inCooldown = false; cooldownFill.style.width = '0%'; setStatus('idle','Siap — Mendeteksi wajah...'); }
        };
        cooldownTimer = requestAnimationFrame(tick);
    }

    // ─── Main scan function ─────────────────────────────────────────
    function doScan() {
        scanning = true;
        scanLine.style.display = 'block';
        setStatus('scanning', 'Menganalisis wajah...');

        const foto = captureFrame();

        fetch(SCAN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ foto_base64: foto, status: mode })
        })
        .then(r => r.json())
        .then(res => {
            scanLine.style.display = 'none';
            scanning = false;

            if (res.success) {
                setStatus('success', `✓ ${res.data.nama}`);
                showResult(true, res.data, '');
                speak(`Halo ${res.data.nama}, ${mode === 'check_in' ? 'selamat datang, kamu sudah masuk' : 'kamu sudah keluar'}.`);
                startCooldown();
                refreshAttendance();
            } else {
                const notFace = res.message && res.message.includes('tidak terdeteksi');
                if (!notFace) {
                    setStatus('error', 'Wajah tidak dikenal');
                    showResult(false, null, res.message);
                    speak('Maaf, wajah tidak terdaftar.');
                    startCooldown();
                } else {
                    setStatus('idle', 'Posisikan wajah ke kamera...');
                }
            }
        })
        .catch(() => {
            scanLine.style.display = 'none';
            scanning = false;
            setStatus('error', 'Error koneksi server');
            showResult(false, null, 'Gagal terhubung ke server.');
            startCooldown();
        });
    }

    // ─── Refresh attendance list ────────────────────────────────────
    function refreshAttendance() {
        fetch(ATT_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(res => {
            const list = res.data || [];
            attCount.textContent = list.length + ' hadir';
            if (list.length === 0) {
                attList.innerHTML = `<div style="padding:30px;text-align:center;color:#475569;"><i class="fas fa-calendar-day fa-2x mb-2 d-block opacity-50"></i><div style="font-size:.82rem;">Belum ada kehadiran hari ini</div></div>`;
                return;
            }
            attList.innerHTML = list.map(a => `
                <div class="att-item">
                    <div class="att-avatar">${a.inisial}</div>
                    <div>
                        <div class="att-name">${a.nama}</div>
                        <div class="att-time">
                            ${a.check_in ? 'IN ' + a.check_in : ''}
                            ${a.check_out ? ' · OUT ' + a.check_out : ''}
                        </div>
                    </div>
                    <span class="att-chip ${a.check_out ? 'out' : 'in'}">${a.check_out ? 'KELUAR' : 'HADIR'}</span>
                </div>`).join('');
        })
        .catch(() => {});
    }

    // ─── Init ───────────────────────────────────────────────────────
    initCamera();
    refreshAttendance();
    setInterval(refreshAttendance, 15000); // refresh tiap 15 detik
    attCount.textContent = '{{ $todayAttendances->count() }} hadir';
});
</script>
@endpush
