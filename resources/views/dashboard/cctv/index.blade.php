@extends('layouts.app')

@section('title', 'Monitoring CCTV Live')
@section('header-title', 'CCTV Monitoring')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<style>
    /* ─── Design Tokens ─────────────────────── */
    :root {
        --nvr-dark:   #0f172a;
        --nvr-panel:  #1e293b;
        --nvr-border: #334155;
        --nvr-accent: #3b82f6;
        --nvr-green:  #22c55e;
        --nvr-red:    #ef4444;
        --nvr-yellow: #f59e0b;
        --nvr-text:   #e2e8f0;
        --nvr-muted:  #94a3b8;
    }

    body { background-color: #f0f4f8 !important; }

    /* ─── Main NVR wrapper ──────────────────── */
    .nvr-wrapper {
        display: grid;
        grid-template-columns: 1fr 340px;
        grid-template-rows: auto 1fr;
        gap: 20px;
        min-height: calc(100vh - 120px);
    }

    /* ─── Camera Grid ───────────────────────── */
    .cam-grid-section { grid-column: 1; }

    .cam-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
    }

    .cam-card {
        background: var(--nvr-dark);
        border-radius: 16px;
        overflow: hidden;
        border: 1.5px solid var(--nvr-border);
        box-shadow: 0 4px 20px rgba(0,0,0,.25);
        transition: transform .2s, box-shadow .2s;
    }
    .cam-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,.3); }
    .cam-card.selected { border-color: var(--nvr-accent); box-shadow: 0 0 0 3px rgba(59,130,246,.3); }

    .cam-header {
        padding: 10px 14px;
        background: rgba(0,0,0,.4);
        display: flex;
        justify-content: space-between;
        align-items: center;
        backdrop-filter: blur(4px);
    }
    .cam-name { color: #fff; font-weight: 700; font-size: .88rem; margin: 0; }
    .cam-badge { font-size: .68rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; }
    .cam-badge.online  { background: rgba(34,197,94,.2);  color: var(--nvr-green); border: 1px solid rgba(34,197,94,.4); }
    .cam-badge.offline { background: rgba(100,116,139,.2); color: #94a3b8; border: 1px solid rgba(100,116,139,.3); }
    .cam-badge.pulse { animation: pulseBadge 1.5s infinite; }

    @keyframes pulseBadge {
        0%  { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
        70% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        100%{ box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    .cam-feed {
        aspect-ratio: 16/9;
        position: relative;
        background: #000;
        display: flex; align-items: center; justify-content: center;
    }
    .cam-feed video, .cam-feed img { width: 100%; height: 100%; object-fit: cover; }

    .cam-offline-overlay {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        color: #475569; gap: 8px;
        font-size: .85rem; font-weight: 600;
    }
    .cam-offline-overlay i { font-size: 2.5rem; opacity: .4; }

    /* Recording indicator */
    .record-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--nvr-red);
        animation: blink 1s infinite;
        display: inline-block;
        margin-right: 6px;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

    .cam-footer {
        padding: 8px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(0,0,0,.3);
    }
    .cam-footer-info { color: var(--nvr-muted); font-size: .72rem; }
    .cam-actions { display: flex; gap: 6px; }
    .cam-btn {
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.1);
        color: var(--nvr-muted);
        border-radius: 6px;
        padding: 4px 8px;
        font-size: .7rem;
        cursor: pointer;
        transition: all .2s;
    }
    .cam-btn:hover { background: rgba(255,255,255,.15); color: #fff; }

    .cam-empty {
        grid-column: 1/-1;
        background: #fff;
        border-radius: 16px;
        padding: 60px 20px;
        text-align: center;
        border: 2px dashed #e2e8f0;
    }
    .cam-empty i { font-size: 3.5rem; color: #cbd5e1; margin-bottom: 16px; }

    /* ─── Sidebar Panel ─────────────────────── */
    .nvr-sidebar { grid-column: 2; display: flex; flex-direction: column; gap: 16px; }

    .panel-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .panel-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }
    .panel-title { font-size: .82rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: .6px; }
    .panel-body { padding: 0; max-height: 350px; overflow-y: auto; }
    .panel-body::-webkit-scrollbar { width: 4px; }
    .panel-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    /* Face Log Items ─────────────────────────────── */
    .face-log-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-bottom: 1px solid #f8fafc;
        transition: background .15s;
    }
    .face-log-item:hover { background: #f8fafc; }
    .face-log-item:last-child { border-bottom: none; }

    .face-thumb {
        width: 38px; height: 38px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .face-thumb-empty {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #e2e8f0, #f1f5f9);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: .75rem;
        color: #94a3b8;
    }
    .face-name { font-size: .82rem; font-weight: 700; color: #0f172a; }
    .face-name.unknown { color: var(--nvr-red); }
    .face-time { font-size: .72rem; color: #94a3b8; font-family: monospace; }

    /* Activity Log Items ──────────────────────────── */
    .act-log-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 16px;
        border-bottom: 1px solid #f8fafc;
    }
    .act-log-item:last-child { border-bottom: none; }
    .act-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .act-dot.motion { background: var(--nvr-yellow); }
    .act-dot.face   { background: var(--nvr-accent); }
    .act-dot.status { background: #94a3b8; }
    .act-log-text { font-size: .8rem; color: #475569; line-height: 1.4; }
    .act-log-time { font-size: .7rem; color: #94a3b8; font-family: monospace; }

    /* ─── Top Status Bar ──────────────────── */
    .nvr-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
    }
    .nvr-live-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--nvr-dark);
        color: var(--nvr-text);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .5px;
    }
    .live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--nvr-green); }
    .live-dot.pulse { animation: pulseDot 1.5s infinite; }
    @keyframes pulseDot {
        0%  { box-shadow: 0 0 0 0 rgba(34,197,94,.6); }
        70% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        100%{ box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    .nvr-clock { font-size: .9rem; font-weight: 800; color: #0f172a; font-family: monospace; letter-spacing: 1px; }

    /* ─── Unknown Alert ───────────────────── */
    .unknown-alert {
        display: flex; align-items: center; gap: 10px;
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 16px;
        animation: shakeAlert .5s ease;
    }
    @keyframes shakeAlert {
        0%,100%{transform:translateX(0)} 25%{transform:translateX(-4px)} 75%{transform:translateX(4px)}
    }

    /* ─── Admin Add Camera btn ──────────────── */
    .btn-nvr-add {
        background: var(--nvr-accent);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 7px 18px;
        font-size: .82rem;
        font-weight: 700;
        transition: all .2s;
    }
    .btn-nvr-add:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59,130,246,.4); }

    /* ─── Camera count badge ─────────────── */
    .cam-count { 
        background: #eff6ff; color: var(--nvr-accent); 
        border-radius: 50px; padding: 2px 10px; 
        font-size: .72rem; font-weight: 800;
    }

    /* ─── Modal ──────────────────────────── */
    .modal-content { border-radius: 20px; border: none; }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
    .modal-body   { padding: 24px; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 16px 24px; }
    .form-label { font-size: .82rem; font-weight: 700; color: #475569; margin-bottom: 6px; }
    .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 9px 14px; }
    .form-control:focus, .form-select:focus { border-color: var(--nvr-accent); box-shadow: 0 0 0 3px rgba(59,130,246,.1); outline:none; }

    /* ─── Responsive ─────────────────────── */
    @media (max-width: 1100px) {
        .nvr-wrapper { grid-template-columns: 1fr; }
        .nvr-sidebar { grid-column: 1; }
    }
    @media print { .cam-actions, .nvr-topbar .btn-nvr-add, .nvr-topbar button { display:none !important; } }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- ═══ TOP STATUS BAR ═══════════════════════════════════════════════ --}}
    <div class="nvr-topbar mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="nvr-live-pill">
                <span class="live-dot pulse" id="live-dot"></span>
                LIVE MONITORING
            </span>
            <span id="last-updated" style="font-size:.78rem;color:#64748b;">Memuat data...</span>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="nvr-clock" id="cctv-clock">{{ now()->format('H:i:s') }}</span>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill"
                    style="font-size:.78rem;font-weight:700;">
                <i class="fas fa-print me-1"></i>Cetak
            </button>
            @if(auth()->user()->role === 'admin')
                <button class="btn-nvr-add" data-bs-toggle="modal" data-bs-target="#modalAddCam">
                    <i class="fas fa-plus me-1"></i>Tambah Kamera
                </button>
            @endif
        </div>
    </div>

    {{-- ═══ UNKNOWN-FACE ALERT ═══════════════════════════════════════════ --}}
    <div id="unknown-alert" class="unknown-alert d-none">
        <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
        <div>
            <div class="fw-bold text-danger" style="font-size:.88rem;">Peringatan: Wajah Asing Terdeteksi!</div>
            <div id="unknown-alert-detail" class="text-muted" style="font-size:.8rem;"></div>
        </div>
    </div>

    {{-- ═══ MAIN GRID ══════════════════════════════════════════════════════ --}}
    <div class="nvr-wrapper">

        {{-- ─── Camera Grid ──────────────────────────────────────────────── --}}
        <div class="cam-grid-section">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0" style="color:#0f172a;">Kamera Aktif</h6>
                    <span class="cam-count">{{ $cameras->count() }} kamera</span>
                </div>
                <div style="font-size:.75rem;color:#94a3b8;">
                    Update otomatis setiap 10 detik — Stream langsung dari IP kamera
                </div>
            </div>

            <div class="cam-grid">
                @foreach($cameras as $camera)
                <div class="cam-card" id="cam-card-{{ $camera->kamera_id }}">
                    {{-- Header --}}
                    <div class="cam-header">
                        <p class="cam-name">
                            <span class="record-dot"></span>{{ $camera->nama }}
                            @if($camera->lokasi)
                                <span style="opacity:.6;font-weight:400;font-size:.75rem;margin-left:4px;">
                                    · {{ $camera->lokasi }}
                                </span>
                            @endif
                        </p>
                        <span id="badge-{{ $camera->kamera_id }}"
                              class="cam-badge {{ $camera->is_online ? 'online pulse' : 'offline' }}">
                            {{ $camera->is_online ? '● ONLINE' : '○ OFFLINE' }}
                        </span>
                    </div>

                    {{-- Feed --}}
                    <div class="cam-feed">
                        @if($camera->is_online && !empty($camera->hls_url))
                            @if(str_contains($camera->hls_url, '/video_feed/') || str_contains($camera->hls_url, '.mjpg') || str_contains($camera->hls_url, ':5000') || str_contains($camera->hls_url, ':8080'))
                                {{-- MJPEG Stream (Python Flask / Raspberry Pi) --}}
                                <img src="{{ $camera->hls_url }}"
                                     alt="Stream {{ $camera->nama }}"
                                     onerror="this.parentElement.innerHTML='<div class=\'cam-offline-overlay\'><i class=\'fas fa-plug\'></i><span>Stream tidak terhubung</span></div>'">
                            @elseif(str_contains($camera->hls_url, '.m3u8'))
                                {{-- HLS Stream --}}
                                <video id="vid-{{ $camera->kamera_id }}" autoplay muted playsinline></video>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const v = document.getElementById('vid-{{ $camera->kamera_id }}');
                                        const src = '{{ $camera->hls_url }}';
                                        if (Hls.isSupported()) {
                                            const hls = new Hls({ enableWorker: false });
                                            hls.loadSource(src);
                                            hls.attachMedia(v);
                                            hls.on(Hls.Events.MANIFEST_PARSED, () => v.play().catch(()=>{}));
                                            hls.on(Hls.Events.ERROR, (e, d) => {
                                                if (d.fatal) { v.parentElement.innerHTML = '<div class="cam-offline-overlay"><i class="fas fa-video-slash"></i><span>HLS Error</span></div>'; }
                                            });
                                        } else if (v.canPlayType('application/vnd.apple.mpegurl')) {
                                            v.src = src; v.play().catch(()=>{});
                                        }
                                    });
                                </script>
                            @else
                                {{-- Direct URL / RTSP through proxy --}}
                                <video id="vid-{{ $camera->kamera_id }}" autoplay muted playsinline controls>
                                    <source src="{{ $camera->hls_url }}" type="video/mp4">
                                </video>
                            @endif
                        @else
                            <div class="cam-offline-overlay">
                                <i class="fas fa-video-slash"></i>
                                <span>{{ $camera->is_online ? 'URL kamera belum dikonfigurasi' : 'Kamera Offline' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="cam-footer">
                        <span class="cam-footer-info">
                            <i class="fas fa-satellite-dish me-1"></i>
                            <span id="ping-{{ $camera->kamera_id }}">
                                Ping: {{ $camera->last_ping ? \Carbon\Carbon::parse($camera->last_ping)->diffForHumans() : 'Belum ada ping' }}
                            </span>
                        </span>
                        <div class="cam-actions">
                            @if(auth()->user()->role === 'admin')
                                <button class="cam-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditCam{{ $camera->id }}"
                                        title="Edit Kamera">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <button class="cam-btn text-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDelCam{{ $camera->id }}"
                                        title="Hapus Kamera">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                @if($cameras->isEmpty())
                <div class="cam-empty">
                    <i class="fas fa-camera d-block"></i>
                    <h5 class="fw-bold text-secondary">Belum Ada Kamera Terdaftar</h5>
                    <p class="text-muted mb-0">
                        @if(auth()->user()->role === 'admin')
                            Klik <strong>Tambah Kamera</strong> untuk menambahkan IP kamera baru
                        @else
                            Hubungi admin untuk menambahkan kamera CCTV
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- ─── Sidebar Panel ────────────────────────────────────────────── --}}
        <div class="nvr-sidebar">

            {{-- ── Face Recognition Log ─────────────────────────────── --}}
            <div class="panel-card">
                <div class="panel-header">
                    <span class="panel-title"><i class="fas fa-id-badge me-2 text-primary"></i>Deteksi Wajah</span>
                    <span class="badge bg-primary rounded-pill" style="font-size:.65rem;">Real-time</span>
                </div>
                <div class="panel-body" id="face-log-container">
                    @forelse($faceLogs as $flog)
                    <div class="face-log-item {{ $flog->status === 'tidak_dikenal' ? 'bg-danger-subtle' : '' }}">
                        @if($flog->foto_capture_path)
                            <img src="{{ asset('storage/'.$flog->foto_capture_path) }}"
                                 class="face-thumb glightbox-trigger"
                                 data-src="{{ asset('storage/'.$flog->foto_capture_path) }}"
                                 title="{{ $flog->child->nama ?? 'Wajah Asing' }}">
                        @else
                            <div class="face-thumb-empty">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <div class="face-name {{ $flog->status === 'tidak_dikenal' ? 'unknown' : '' }}">
                                {{ $flog->status === 'tidak_dikenal' ? '⚠ Wajah Asing' : ($flog->child->nama ?? 'Seseorang') }}
                            </div>
                            <div class="face-time">
                                {{ $flog->waktu_deteksi->format('H:i:s') }} — Cam: {{ strtoupper($flog->kamera_id) }}
                            </div>
                        </div>
                        <div>
                            @if($flog->status === 'check_in')
                                <span class="badge" style="background:#dcfce7;color:#15803d;font-size:.65rem;">C-IN</span>
                            @elseif($flog->status === 'tidak_dikenal')
                                <span class="badge bg-danger" style="font-size:.65rem;">ASING</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:.65rem;">
                                    {{ strtoupper(str_replace('_',' ',$flog->status)) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="padding:40px;text-align:center;color:#94a3b8;">
                        <i class="fas fa-robot fa-2x mb-2 d-block opacity-50"></i>
                        <div style="font-size:.82rem;">Belum ada deteksi wajah</div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- ── Activity Log ─────────────────────────────────────── --}}
            <div class="panel-card">
                <div class="panel-header">
                    <span class="panel-title"><i class="fas fa-list-ul me-2 text-warning"></i>Log Aktivitas</span>
                    <span class="badge" style="background:#fef9c3;color:#a16207;font-size:.65rem;">Motion &amp; Events</span>
                </div>
                <div class="panel-body" id="activity-log-container">
                    @forelse($activityLogs as $log)
                    <div class="act-log-item">
                        <span class="act-dot {{ $log->jenis_aktivitas === 'motion' || $log->jenis_aktivitas === 'motion_detected' ? 'motion' : 'status' }}"></span>
                        <div>
                            <div class="act-log-text">{{ $log->keterangan ?? $log->jenis_aktivitas }}</div>
                            <div class="act-log-time">{{ $log->waktu->format('H:i:s') }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="padding:40px;text-align:center;color:#94a3b8;">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                        <div style="font-size:.82rem;">Tidak ada kejadian mencurigakan</div>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════ MODALS Admin ═══════════════════════════════════ --}}
@if(auth()->user()->role === 'admin')

{{-- Add Camera --}}
<div class="modal fade" id="modalAddCam" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Kamera CCTV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.cctv.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Kamera *</label>
                            <input type="text" class="form-control" name="kamera_id" placeholder="cam_01" required>
                            <div class="form-text">Huruf kecil, tanpa spasi</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Kamera *</label>
                            <input type="text" class="form-control" name="nama" placeholder="Ruang Tamu" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL Stream (HLS/MJPEG)</label>
                            <input type="text" class="form-control" name="hls_url"
                                   placeholder="http://192.168.1.xx:5000/video_feed/cam_01">
                            <div class="form-text">Mendukung: MJPEG, HLS (.m3u8), URL langsung</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL RTSP (Opsional)</label>
                            <input type="text" class="form-control" name="rtsp_url"
                                   placeholder="rtsp://admin:pass@192.168.1.xx:554/stream">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="lokasi" placeholder="Ruang Belakang">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active_add" checked>
                                <label class="form-check-label form-label mb-0" for="is_active_add">Kamera Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit & Delete modals per camera --}}
@foreach($cameras as $camera)
{{-- Edit Modal --}}
<div class="modal fade" id="modalEditCam{{ $camera->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-cog me-2 text-warning"></i>Edit Kamera: {{ $camera->nama }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.cctv.update', $camera->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Kamera *</label>
                            <input type="text" class="form-control" name="nama" value="{{ $camera->nama }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL Stream (HLS/MJPEG)</label>
                            <input type="text" class="form-control" name="hls_url" value="{{ $camera->hls_url }}"
                                   placeholder="http://192.168.1.xx:5000/video_feed/cam_01">
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL RTSP</label>
                            <input type="text" class="form-control" name="rtsp_url" value="{{ $camera->rtsp_url }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="lokasi" value="{{ $camera->lokasi }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active_{{ $camera->id }}" {{ $camera->is_active ? 'checked' : '' }}>
                                <label class="form-check-label form-label mb-0" for="is_active_{{ $camera->id }}">Kamera Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="modalDelCam{{ $camera->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pb-1">
                <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                <h6 class="fw-bold">Hapus Kamera?</h6>
                <p class="text-muted" style="font-size:.85rem;">
                    Kamera <strong>{{ $camera->nama }}</strong> akan dihapus permanen beserta semua log aktivitasnya.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.cctv.destroy', $camera->id) }}" method="POST" class="m-0">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endif
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
//  NVR LIVE POLLING  — Interval 10 detik, tanpa reload halaman
//  Endpoint: /dashboard/cctv/live-data  (JSON)
// ═══════════════════════════════════════════════════════════════════════════

const POLL_URL      = "{{ route('dashboard.cctv.live') }}";
const POLL_INTERVAL = 10000;

// ── Real-time clock ─────────────────────────────────────────────────────
setInterval(() => {
    const d = new Date();
    const el = document.getElementById('cctv-clock');
    if (el) el.textContent = d.toLocaleTimeString('id-ID', { hour12: false });
}, 1000);

// ── Render: camera status badges ────────────────────────────────────────
function renderCameraStatus(cameras) {
    cameras.forEach(cam => {
        const badge = document.getElementById(`badge-${cam.kamera_id}`);
        const ping  = document.getElementById(`ping-${cam.kamera_id}`);
        if (badge) {
            badge.className = `cam-badge ${cam.is_online ? 'online pulse' : 'offline'}`;
            badge.textContent = cam.is_online ? '● ONLINE' : '○ OFFLINE';
        }
        if (ping) ping.textContent = `Ping: ${cam.last_ping}`;
    });
}

// ── Render: face log list ────────────────────────────────────────────────
function renderFaceLogs(logs) {
    const container = document.getElementById('face-log-container');
    if (!container) return;

    if (!logs || logs.length === 0) {
        container.innerHTML = `
            <div style="padding:40px;text-align:center;color:#94a3b8;">
                <i class="fas fa-robot fa-2x mb-2 d-block opacity-50"></i>
                <div style="font-size:.82rem;">Belum ada deteksi wajah</div>
            </div>`;
        return;
    }

    container.innerHTML = logs.map(log => `
        <div class="face-log-item ${log.is_unknown ? 'bg-danger-subtle' : ''}">
            ${log.foto
                ? `<img src="${log.foto}" class="face-thumb">`
                : `<div class="face-thumb-empty"><i class="fas fa-user"></i></div>`}
            <div class="flex-grow-1" style="min-width:0;">
                <div class="face-name ${log.is_unknown ? 'unknown' : ''}">${log.nama}</div>
                <div class="face-time">${log.waktu} — ${log.kamera_id}</div>
            </div>
            <div>
                <span class="badge ${log.status === 'check_in' ? '' : log.is_unknown ? 'bg-danger' : 'bg-secondary'}"
                      style="${log.status==='check_in'?'background:#dcfce7;color:#15803d;':''} font-size:.65rem;">
                    ${log.status === 'check_in' ? 'C-IN' : log.is_unknown ? 'ASING' : log.status_label}
                </span>
            </div>
        </div>
    `).join('');
}

// ── Render: activity log list ────────────────────────────────────────────
function renderActivityLogs(logs) {
    const container = document.getElementById('activity-log-container');
    if (!container) return;

    if (!logs || logs.length === 0) {
        container.innerHTML = `
            <div style="padding:40px;text-align:center;color:#94a3b8;">
                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                <div style="font-size:.82rem;">Tidak ada kejadian mencurigakan</div>
            </div>`;
        return;
    }

    container.innerHTML = logs.map(log => `
        <div class="act-log-item">
            <span class="act-dot ${(log.jenis_aktivitas === 'motion' || log.jenis_aktivitas === 'motion_detected') ? 'motion' : 'status'}"></span>
            <div>
                <div class="act-log-text">${log.keterangan || log.jenis_aktivitas}</div>
                <div class="act-log-time">${log.waktu}</div>
            </div>
        </div>
    `).join('');
}

// ── Render: unknown face alert ───────────────────────────────────────────
function renderUnknownAlert(count) {
    const el = document.getElementById('unknown-alert');
    const detail = document.getElementById('unknown-alert-detail');
    if (!el) return;
    if (count > 0) {
        el.classList.remove('d-none');
        if (detail) detail.textContent = `${count} wajah tidak dikenal terdeteksi hari ini. Harap periksa log kamera.`;
    } else {
        el.classList.add('d-none');
    }
}

// ── Main polling function ────────────────────────────────────────────────
function fetchLiveData() {
    fetch(POLL_URL, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => { if (!res.ok) throw new Error(`HTTP ${res.status}`); return res.json(); })
    .then(data => {
        renderCameraStatus(data.cameras   ?? []);
        renderFaceLogs(data.face_logs     ?? []);
        renderActivityLogs(data.activity_logs ?? []);
        renderUnknownAlert(data.unknown_today ?? 0);

        // Update live indicator
        const ts = document.getElementById('last-updated');
        if (ts) ts.textContent = `Diperbarui pukul ${data.updated_at}`;

        // Blink live dot
        const dot = document.getElementById('live-dot');
        if (dot) { dot.style.opacity = '0'; setTimeout(() => dot.style.opacity = '1', 300); }
    })
    .catch(err => console.warn('[NVR Polling] Error:', err));
}

// ── GLightbox ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    GLightbox({ selector: '.glightbox-trigger' });
    fetchLiveData();                           // immediate first call
    setInterval(fetchLiveData, POLL_INTERVAL); // every 10s
});
</script>
@endpush