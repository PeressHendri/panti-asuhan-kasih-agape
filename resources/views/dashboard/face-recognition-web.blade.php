@extends('layouts.app')
@section('title', 'Absen Wajah (Webcam)')
@section('header-title', 'Absen Wajah (Webcam)')

@push('styles')
<style>
:root {
    --fr-bg: #f8fafc;
    --fr-card: #ffffff;
    --fr-border: #e2e8f0;
    --fr-accent: #3b82f6;
    --fr-green: #10b981;
    --fr-yellow: #f59e0b;
    --fr-red: #ef4444;
    --fr-text-main: #0f172a;
    --fr-text-muted: #64748b;
}

.fr-wrapper { display: grid; grid-template-columns: 1fr 420px; gap: 24px; }
@media(max-width:992px){ .fr-wrapper { grid-template-columns: 1fr; } }

/* Camera Card */
.cam-card {
    background: var(--fr-card);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid var(--fr-border);
    box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
}
.cam-header {
    background: #ffffff;
    padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--fr-border);
}
.cam-header h5 { color: var(--fr-text-main); margin:0; font-weight:800; font-size:1.05rem; }
.live-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.2);
    color:#ef4444; padding:4px 12px; border-radius:50px; font-size:.75rem; font-weight:800;
}
.live-dot { width:8px;height:8px;border-radius:50%;background:#ef4444;animation:pulseDot 1.5s infinite; }
@keyframes pulseDot{0%{box-shadow:0 0 0 0 rgba(239,68,68,.4)}70%{box-shadow:0 0 0 6px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}

.cam-body { position:relative; background:#f1f5f9; min-height:420px; display:flex; align-items:center; justify-content:center; }
#webcam { width:100%; max-height:420px; object-fit:cover; transform:scaleX(-1); display:block; border-radius: 0; }

/* Clean look - tidak ada panduan visual AI */
/* Status bar */
.cam-status {
    background:#ffffff; padding:16px 24px;
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.status-badge {
    display:inline-flex; align-items:center; gap:8px;
    padding:6px 16px; border-radius:50px; font-size:.8rem; font-weight:700; border:1px solid;
}
.status-badge.scanning { background:rgba(59,130,246,.1); border-color:rgba(59,130,246,.3); color:#2563eb; }
.status-badge.idle     { background:#f1f5f9; border-color:#cbd5e1; color:#475569; }
.status-badge.success  { background:rgba(16,185,129,.1);  border-color:rgba(16,185,129,.3);  color:#059669; }
.status-badge.error    { background:rgba(239,68,68,.1);   border-color:rgba(239,68,68,.3);   color:#dc2626; }

.mode-toggle { display:flex; gap:8px; }
.mode-btn {
    padding:6px 16px; border-radius:50px; border:1.5px solid;
    font-size:.8rem; font-weight:700; cursor:pointer; transition:all .2s; background: #fff;
}
.mode-btn.checkin  { border-color:var(--fr-green); color:var(--fr-green); }
.mode-btn.checkin.active  { background:var(--fr-green); color:#fff; box-shadow: 0 4px 12px rgba(16,185,129,.3); }
.mode-btn.checkout { border-color:var(--fr-yellow); color:var(--fr-yellow); }
.mode-btn.checkout.active { background:var(--fr-yellow); color:#fff; box-shadow: 0 4px 12px rgba(245,158,11,.3); }

/* Cooldown bar */
.cooldown-bar { height:4px; background:#f1f5f9; margin:0; overflow:hidden; }
.cooldown-fill { height:100%; background:var(--fr-accent); width:0; transition:width .1s linear; }

/* Result Panel */
.result-panel {
    background: var(--fr-card);
    border-radius:24px;
    border:1px solid var(--fr-border);
    box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
    overflow:hidden;
    display:flex; flex-direction:column;
}
.result-header {
    padding:16px 24px;
    background: #f8fafc;
    border-bottom:1px solid var(--fr-border);
    display:flex; align-items:center; justify-content:space-between;
}
.result-header h6 { color:var(--fr-text-main); margin:0; font-weight:800; font-size:1rem; }

/* Last result box */
.last-result {
    padding:24px; border-bottom:1px solid var(--fr-border);
    min-height:220px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
}
.result-avatar {
    width:90px; height:90px; border-radius:50%; object-fit:cover;
    border:4px solid var(--fr-green); box-shadow:0 8px 25px rgba(16,185,129,.4);
    margin-bottom:16px;
}
.result-name { font-size:1.2rem; font-weight:800; color:var(--fr-text-main); margin-bottom:6px; }
.result-meta { font-size:.85rem; color:var(--fr-text-muted); margin-bottom:12px; font-weight: 500; }
.result-badge {
    display:inline-block; padding:4px 16px; border-radius:50px; font-size:.75rem; font-weight:800;
}
.result-badge.ok  { background:rgba(16,185,129,.1); color:#059669; border:1px solid rgba(16,185,129,.3); }
.result-badge.err { background:rgba(239,68,68,.1);  color:#dc2626; border:1px solid rgba(239,68,68,.3); }
.result-badge.wait{ background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }

/* Attendance list */
.att-list { flex:1; overflow-y:auto; background: #fff; }
.att-list::-webkit-scrollbar{ width:6px; }
.att-list::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:6px; }
.att-item {
    display:flex; align-items:center; gap:12px;
    padding:12px 20px; border-bottom:1px solid #f1f5f9;
    transition:background .2s;
}
.att-item:hover{ background:#f8fafc; }
.att-avatar {
    width:40px; height:40px; border-radius:12px; flex-shrink:0;
    background:linear-gradient(135deg,#3b82f6,#6366f1);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.9rem; box-shadow: 0 4px 10px rgba(59,130,246,.3);
}
.att-name { font-size:.9rem; font-weight:700; color:var(--fr-text-main); margin-bottom:2px; }
.att-time { font-size:.75rem; color:var(--fr-text-muted); font-family: 'Courier New', monospace; font-weight:600; }
.att-chip {
    margin-left:auto; padding:4px 12px; border-radius:50px; font-size:.7rem; font-weight:800;
}
.att-chip.in  { background:rgba(16,185,129,.1);  color:#059669; }
.att-chip.out { background:rgba(245,158,11,.1); color:#d97706; }

/* Camera overlay */
#cam-overlay {
    position:absolute; inset:0; background:rgba(255,255,255,.9);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    z-index:10; color:var(--fr-text-main); text-align:center; backdrop-filter: blur(4px);
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
            {{-- Mode otomatis: tidak perlu tombol --}}
            <span id="auto-mode-badge" style="
                display:inline-flex;align-items:center;gap:6px;
                background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);
                color:#3b82f6;padding:5px 14px;border-radius:50px;font-size:.78rem;font-weight:700;
            ">
                <i class="fas fa-magic me-1"></i> Mode Otomatis
            </span>
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
        <div style="padding:10px 24px 6px;border-bottom:1px solid var(--fr-border);background:#f8fafc;">
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

    // ─── Konfigurasi ─────────────────────────────────────────────────
    const SCAN_INTERVAL = 4000;   // ms antar scan otomatis
    const COOLDOWN_MS   = 8000;   // ms cooldown setelah scan berhasil
    const SCAN_URL      = '{{ route("dashboard.face-recognition.web-scan") }}';
    const ATT_URL       = '{{ route("dashboard.face-recognition.recent-attendance") }}';
    const CSRF          = '{{ csrf_token() }}';

    let scanning    = false;
    let inCooldown  = false;
    let cameraReady = false;
    let scanTimer   = null;
    let cooldownTimer = null;

    // ─── DOM ──────────────────────────────────────────────────────────
    const video       = document.getElementById('webcam');
    const overlay     = document.getElementById('cam-overlay');
    const retryBtn    = document.getElementById('btn-retry');
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

    // ─── Camera init ──────────────────────────────────────────────────
    function initCamera() {
        navigator.mediaDevices.getUserMedia({ video: { width:640, height:480, facingMode:'user' } })
        .then(stream => {
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
                overlay.style.display = 'none';
                cameraReady = true;
                setStatus('idle', 'Siap memindai...');
                startAutoScan();
            };
        })
        .catch(() => {
            overlay.querySelector('h5').innerText = 'Akses Kamera Ditolak';
            overlay.querySelector('p').innerText  = 'Periksa izin kamera di browser.';
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

    // ─── Status helper ────────────────────────────────────────────────
    function setStatus(type, text) {
        statusBadge.className = `status-badge ${type}`;
        statusText.innerText  = text;
        spinIcon.classList.toggle('d-none', type !== 'scanning');
        eyeIcon.classList.toggle('d-none',  type === 'scanning');
    }

    // ─── Auto scan loop ───────────────────────────────────────────────
    function startAutoScan() {
        if (scanTimer) clearInterval(scanTimer);
        scanTimer = setInterval(() => {
            if (!cameraReady || scanning || inCooldown) return;
            doScan();
        }, SCAN_INTERVAL);
    }

    // ─── Take snapshot ────────────────────────────────────────────────
    function captureFrame() {
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        return canvas.toDataURL('image/jpeg', 0.9);
    }

    // ─── Speech ───────────────────────────────────────────────────────
    function speak(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'id-ID'; u.rate = 1.05;
        window.speechSynthesis.speak(u);
    }

    // ─── Show last result ─────────────────────────────────────────────
    function showResult(ok, data, msg) {
        if (ok) {
            const isIn  = data.status && data.status.toLowerCase().includes('masuk');
            const color = isIn ? '#10b981' : '#f59e0b';
            const icon  = isIn ? 'sign-in-alt' : 'sign-out-alt';
            lastResult.innerHTML = `
                <img src="${data.foto}" class="result-avatar"
                     style="border-color:${color};box-shadow:0 8px 25px ${color}40;"
                     onerror="this.style.display='none'">
                <div class="result-name">${data.nama}</div>
                <div class="result-meta">Akurasi: <strong style="color:#22c55e;">${parseFloat(data.confidence).toFixed(1)}%</strong> · ${data.waktu}</div>
                <span class="result-badge ok" style="background:${color}20;color:${color};border-color:${color}50;">
                    <i class="fas fa-${icon} me-1"></i>${data.status}
                </span>`;
        } else {
            const isDone = msg && msg.includes('sudah menyelesaikan');
            lastResult.innerHTML = `
                <div style="width:70px;height:70px;border-radius:50%;
                     background:${isDone ? 'rgba(59,130,246,.1)' : 'rgba(239,68,68,.1)'};
                     border:2px solid ${isDone ? 'rgba(59,130,246,.3)' : 'rgba(239,68,68,.3)'};
                     display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <i class="fas fa-${isDone ? 'check-double' : 'user-times'} fa-2x"
                       style="color:${isDone ? '#3b82f6' : '#ef4444'};"></i>
                </div>
                <span class="result-badge ${isDone ? '' : 'err'}"
                      style="${isDone ? 'background:rgba(59,130,246,.1);color:#2563eb;border:1px solid rgba(59,130,246,.3);' : ''}">
                    ${isDone ? '✓ SELESAI HARI INI' : 'TIDAK DIKENAL'}
                </span>
                <p class="text-muted small mt-2 mb-0">${msg}</p>`;
        }
    }

    // ─── Cooldown bar ─────────────────────────────────────────────────
    function startCooldown() {
        inCooldown = true;
        const start = Date.now();
        const tick  = () => {
            const pct = Math.min(100, ((Date.now() - start) / COOLDOWN_MS) * 100);
            cooldownFill.style.width = pct + '%';
            if (pct < 100) { cooldownTimer = requestAnimationFrame(tick); }
            else { inCooldown = false; cooldownFill.style.width = '0%'; setStatus('idle', 'Siap memindai...'); }
        };
        cooldownTimer = requestAnimationFrame(tick);
    }

    // ─── Main scan function ───────────────────────────────────────────
    function doScan() {
        scanning = true;
        setStatus('scanning', 'Memindai...');

        const foto = captureFrame();

        // Kirim HANYA foto — status (check_in/check_out) ditentukan otomatis server
        fetch(SCAN_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify({ foto_base64: foto })
        })
        .then(r  => r.json())
        .then(res => {
            scanning = false;

            if (res.success) {
                const isIn = res.data.status && res.data.status.toLowerCase().includes('masuk');
                setStatus('success', `✓ ${res.data.nama} — ${isIn ? 'Check In' : 'Check Out'}`);
                showResult(true, res.data, '');
                speak(isIn
                    ? `Selamat datang ${res.data.nama}, Check In berhasil.`
                    : `${res.data.nama}, Check Out berhasil. Sampai jumpa.`);
                startCooldown();
                refreshAttendance();
            } else {
                const notFace = res.message && res.message.includes('tidak terdeteksi');
                const isDone  = res.message && res.message.includes('sudah menyelesaikan');
                if (!notFace) {
                    if (isDone) {
                        setStatus('idle', 'Absensi hari ini sudah selesai ✓');
                    } else {
                        setStatus('error', 'Wajah tidak dikenal');
                        speak('Maaf, wajah tidak dikenal.');
                    }
                    showResult(false, null, res.message);
                    startCooldown();
                } else {
                    setStatus('idle', 'Siap memindai...');
                }
            }
        })
        .catch(() => {
            scanning = false;
            setStatus('error', 'Error koneksi server');
            showResult(false, null, 'Gagal terhubung ke server.');
            startCooldown();
        });
    }

    // ─── Refresh attendance list ──────────────────────────────────────
    function refreshAttendance() {
        fetch(ATT_URL, { headers: { 'Accept': 'application/json' } })
        .then(r  => r.json())
        .then(res => {
            const list = res.data || [];
            attCount.textContent = list.length + ' hadir';
            if (list.length === 0) {
                attList.innerHTML = `<div style="padding:30px;text-align:center;color:#475569;">
                    <i class="fas fa-calendar-day fa-2x mb-2 d-block opacity-50"></i>
                    <div style="font-size:.82rem;">Belum ada kehadiran hari ini</div></div>`;
                return;
            }
            attList.innerHTML = list.map(a => `
                <div class="att-item">
                    <div class="att-avatar">${a.inisial}</div>
                    <div>
                        <div class="att-name">${a.nama}</div>
                        <div class="att-time">
                            ${a.check_in  ? '<i class="fas fa-sign-in-alt" style="color:#10b981;font-size:.65rem;"></i> IN '  + a.check_in  : ''}
                            ${a.check_out ? ' · <i class="fas fa-sign-out-alt" style="color:#f59e0b;font-size:.65rem;"></i> OUT ' + a.check_out : ''}
                        </div>
                    </div>
                    <span class="att-chip ${a.check_out ? 'out' : 'in'}">${a.check_out ? 'KELUAR' : 'HADIR'}</span>
                </div>`).join('');
        })
        .catch(() => {});
    }

    // ─── Init ─────────────────────────────────────────────────────────
    initCamera();
    refreshAttendance();
    setInterval(refreshAttendance, 15000);
    attCount.textContent = '{{ $todayAttendances->count() }} hadir';
});
</script>
@endpush
