@extends('layouts.app')

@section('title', 'Manajemen Kehadiran')
@section('header-title', 'Kehadiran Anak')

@push('styles')
<style>
    /* ─── Design Tokens ──────────────────────────────────── */
    :root {
        --att-blue:   #3b82f6;
        --att-green:  #22c55e;
        --att-yellow: #f59e0b;
        --att-red:    #ef4444;
        --att-purple: #8b5cf6;
    }

    /* ─── Stat Cards ─────────────────────────────────────── */
    .stat-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 20px 24px;
        transition: transform .2s, box-shadow .2s;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .stat-card.blue::before   { background: var(--att-blue); }
    .stat-card.green::before  { background: var(--att-green); }
    .stat-card.yellow::before { background: var(--att-yellow); }
    .stat-card.red::before    { background: var(--att-red); }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.08); }

    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }
    .stat-icon.blue   { background: #eff6ff; color: var(--att-blue); }
    .stat-icon.green  { background: #f0fdf4; color: var(--att-green); }
    .stat-icon.yellow { background: #fffbeb; color: var(--att-yellow); }
    .stat-icon.red    { background: #fef2f2; color: var(--att-red); }

    .stat-label { font-size: .72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .8px; font-weight: 700; }
    .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-top: 4px; color: #0f172a; }

    /* ─── Status Badge ───────────────────────────────────── */
    .badge-hadir  { background:#dcfce7; color:#15803d; }
    .badge-sakit  { background:#fef9c3; color:#a16207; }
    .badge-izin   { background:#e0f2fe; color:#0369a1; }
    .badge-alpa   { background:#fee2e2; color:#b91c1c; }

    /* ─── Main Card ──────────────────────────────────────── */
    .main-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .main-card-header {
        background: #fff;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    /* ─── Pills Tab ──────────────────────────────────────── */
    .tab-pill { display: flex; gap: 6px; }
    .tab-pill .btn-pill {
        padding: 7px 20px;
        border-radius: 50px;
        border: none;
        font-size: .83rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #64748b;
        transition: all .2s;
        cursor: pointer;
    }
    .tab-pill .btn-pill.active {
        background: var(--att-blue);
        color: #fff;
        box-shadow: 0 4px 12px rgba(59,130,246,.35);
    }

    /* ─── Checkin Quick Form ─────────────────────────────── */
    .quick-form {
        display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
    }
    .quick-form select, .quick-form input {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        padding: 7px 14px;
        font-size: .83rem;
        font-weight: 600;
        color: #334155;
    }
    .quick-form select:focus, .quick-form input:focus {
        border-color: var(--att-blue);
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
        outline: none;
    }
    .btn-checkin {
        background: var(--att-green);
        color: #fff;
        border-radius: 10px;
        border: none;
        padding: 7px 18px;
        font-weight: 700;
        font-size: .83rem;
        transition: all .2s;
    }
    .btn-checkin:hover { background: #16a34a; transform: translateY(-1px); }

    /* ─── Table ──────────────────────────────────────────── */
    .att-table { width: 100%; border-collapse: collapse; }
    .att-table thead th {
        padding: 12px 16px;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .7px;
        font-weight: 700;
        color: #94a3b8;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .att-table tbody tr {
        border-bottom: 1px solid #f8fafc;
        transition: background .15s;
    }
    .att-table tbody tr:hover { background: #f8fafc; }
    .att-table td { padding: 13px 16px; font-size: .88rem; color: #334155; vertical-align: middle; }

    /* ─── Avatar ─────────────────────────────────────────── */
    .child-avatar {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .85rem;
    }

    /* ─── Time Badge ─────────────────────────────────────── */
    .time-badge {
        font-size: .8rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 8px;
        font-family: monospace;
        letter-spacing: .5px;
    }
    .time-badge.active-out {
        background: #fef9c3;
        color: #b45309;
    }

    /* ─── Filter Bar ─────────────────────────────────────── */
    .filter-bar {
        display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
    }
    .filter-bar select, .filter-bar input {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        padding: 7px 14px;
        font-size: .83rem;
        font-weight: 600;
        color: #334155;
    }
    .filter-bar select:focus, .filter-bar input:focus {
        border-color: var(--att-blue);
        outline: none;
    }

    /* ─── Status Pills ───────────────────────────────────── */
    .status-pill {
        padding: 3px 12px;
        border-radius: 50px;
        font-size: .75rem;
        font-weight: 700;
        display: inline-block;
    }

    /* ─── Checkout btn ───────────────────────────────────── */
    .btn-checkout {
        background: #fff7ed;
        border: 1.5px solid #fed7aa;
        color: #c2410c;
        border-radius: 8px;
        padding: 4px 12px;
        font-size: .78rem;
        font-weight: 700;
        transition: all .2s;
        cursor: pointer;
    }
    .btn-checkout:hover { background: #ffedd5; transform: translateY(-1px); }

    .btn-delete {
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        color: #dc2626;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: .78rem;
        transition: all .2s;
        cursor: pointer;
    }
    .btn-delete:hover { background: #fee2e2; }

    /* ─── AI Log rows ────────────────────────────────────── */
    tr.row-unknown { background: #fff5f5 !important; }
    tr.row-unknown:hover { background: #fee2e2 !important; }

    /* ─── Pi Status Pill ─────────────────────────────────── */
    .pi-status {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: .78rem;
        font-weight: 700;
        border: 1.5px solid;
    }
    .pi-status.online  { color: #15803d; background: #f0fdf4; border-color: #bbf7d0; }
    .pi-status.offline { color: #b91c1c; background: #fef2f2; border-color: #fecaca; }
    .pi-dot { width: 8px; height: 8px; border-radius: 50%; }
    .pi-dot.online  { background: #22c55e; animation: pulseDot 1.5s infinite; }
    .pi-dot.offline { background: #ef4444; }

    @keyframes pulseDot {
        0%  { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
        70% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        100%{ box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    /* ─── Check-In Modal ──────────────────────────────────── */
    .modal-content { border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,.15); }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
    .modal-body { padding: 24px; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 16px 24px; }
    .form-label { font-size: .83rem; font-weight: 700; color: #475569; margin-bottom: 6px; }
    .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 9px 14px; font-size: .88rem; }
    .form-control:focus, .form-select:focus { border-color: var(--att-blue); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

    .empty-state { padding: 60px 20px; text-align: center; color: #94a3b8; }
    .empty-state i { font-size: 3.5rem; margin-bottom: 16px; opacity: .4; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- ── Top Bar ─────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

        {{-- Pi Status --}}
        <div class="d-flex align-items-center gap-2">
            @if($isPiOnline)
                <span class="pi-status online">
                    <span class="pi-dot online"></span>Sistem AI Aktif
                </span>
            @else
                <span class="pi-status offline">
                    <span class="pi-dot offline"></span>Sistem AI Offline
                </span>
            @endif
        </div>

        {{-- Filter + Export --}}
        <form action="{{ route('admin.attendance') }}" method="GET" class="filter-bar">
            <span class="text-muted small fw-semibold">Tampilkan:</span>
            <select name="range" onchange="this.form.submit()">
                <option value="today"  {{ request('range','today')=='today'  ? 'selected':'' }}>Hari Ini</option>
                <option value="week"   {{ request('range')=='week'    ? 'selected':'' }}>7 Hari</option>
                <option value="month"  {{ request('range')=='month'   ? 'selected':'' }}>30 Hari</option>
                <option value="custom" {{ request('range')=='custom'  ? 'selected':'' }}>Tanggal Pilihan</option>
            </select>
            @if(request('range')=='custom')
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
            @endif
        </form>

        <a href="{{ route('admin.attendance.export', ['range' => request('range','today'), 'date' => $date]) }}"
           class="btn btn-sm btn-success rounded-pill px-4" style="font-weight:700;">
            <i class="fas fa-download me-1"></i> Export CSV
        </a>
    </div>

    {{-- ── Stat Cards ─────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Hadir</div>
                        <div class="stat-value text-primary">{{ $stats['total_attendance'] }}</div>
                    </div>
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card green">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Via AI Face</div>
                        <div class="stat-value text-success">{{ $stats['check_in_ai'] }}</div>
                    </div>
                    <div class="stat-icon green"><i class="fas fa-robot"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card yellow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Deteksi Kamera</div>
                        <div class="stat-value" style="color:var(--att-yellow);">{{ $stats['total_ai_logs'] }}</div>
                    </div>
                    <div class="stat-icon yellow"><i class="fas fa-eye"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card red">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Wajah Asing</div>
                        <div class="stat-value text-danger">{{ $stats['unknown_faces'] }}</div>
                    </div>
                    <div class="stat-icon red"><i class="fas fa-user-secret"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Card ───────────────────────────────────────────── --}}
    <div class="main-card">
        <div class="main-card-header">

            {{-- Tabs --}}
            <div class="tab-pill" id="attTabs">
                <button class="btn-pill active" data-target="tab-daftar">
                    <i class="fas fa-clipboard-list me-1"></i>Daftar Kehadiran
                </button>
                <button class="btn-pill" data-target="tab-kamera">
                    <i class="fas fa-camera me-1"></i>Log Kamera AI
                </button>
            </div>

            {{-- Quick Check-In (hanya jika manual diaktifkan) --}}
            @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false))
            <form action="{{ route('admin.attendance.check-in') }}" method="POST" class="quick-form">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <select name="child_id" required style="min-width:170px;">
                    <option value="">Pilih Anak...</option>
                    @foreach($children as $child)
                        <option value="{{ $child->id }}">{{ $child->nama }}</option>
                    @endforeach
                </select>
                <select name="status" required style="width:110px;">
                    <option value="hadir">✅ Hadir</option>
                    <option value="izin">🟡 Izin</option>
                    <option value="sakit">🔵 Sakit</option>
                    <option value="alpa">🔴 Alpa</option>
                </select>
                <button type="submit" class="btn-checkin">
                    <i class="fas fa-sign-in-alt me-1"></i>Check-In
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" 
                        data-bs-toggle="modal" data-bs-target="#modalManual"
                        style="font-weight:700; font-size:.8rem;">
                    <i class="fas fa-pen me-1"></i>Manual
                </button>
            </form>
            @endif
        </div>

        {{-- ── Tab: Daftar Kehadiran ──────────────────────────────── --}}
        <div id="tab-daftar" class="tab-content-pane">
            <div class="table-responsive">
                <table class="att-table">
                    <thead>
                        <tr>
                            <th style="padding-left:24px;">Anak</th>
                            <th>Tanggal</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Sumber</th>
                            @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false))
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td style="padding-left:24px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="child-avatar">
                                        {{ strtoupper(substr($att->child->nama ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color:#0f172a;">{{ $att->child->nama ?? 'Tidak diketahui' }}</div>
                                        @if($att->note)<div class="text-muted" style="font-size:.75rem;">{{ $att->note }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.85rem;">
                                    {{ \Carbon\Carbon::parse($att->date)->isoFormat('D MMM YYYY') }}
                                </div>
                            </td>
                            <td>
                                @if($att->check_in)
                                    <span class="time-badge">{{ $att->check_in->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($att->check_out)
                                    <span class="time-badge">{{ $att->check_out->format('H:i') }}</span>
                                @else
                                    @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) && $att->check_in && $att->status === 'hadir')
                                        <form action="{{ route('admin.attendance.check-out', $att->id) }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            <button type="submit" class="btn-checkout">
                                                <i class="fas fa-sign-out-alt me-1"></i>Check-Out
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($att->check_in && $att->check_out)
                                    @php
                                        $dur = $att->check_in->diffInMinutes($att->check_out);
                                        $h = intdiv($dur, 60); $m = $dur % 60;
                                    @endphp
                                    <span class="time-badge">{{ $h > 0 ? $h.'j '.$m.'m' : $m.'m' }}</span>
                                @elseif($att->check_in && !$att->check_out && $att->status === 'hadir')
                                    <span class="time-badge active-out">Aktif</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $cls = ['hadir'=>'badge-hadir','sakit'=>'badge-sakit','izin'=>'badge-izin','alpa'=>'badge-alpa'];
                                    $lbl = ['hadir'=>'Hadir','sakit'=>'Sakit','izin'=>'Izin','alpa'=>'Alpa'];
                                @endphp
                                <span class="status-pill {{ $cls[$att->status] ?? 'badge-alpa' }}">
                                    {{ $lbl[$att->status] ?? $att->status }}
                                </span>
                            </td>
                            <td>
                                @if($att->algoritma === 'manual')
                                    <span class="badge bg-secondary rounded-pill" style="font-size:.7rem;">Manual</span>
                                @else
                                    <span class="badge rounded-pill" style="background:#ede9fe;color:#6d28d9;font-size:.7rem;">
                                        <i class="fas fa-robot me-1"></i>{{ strtoupper($att->algoritma ?? 'AI') }}
                                    </span>
                                @endif
                            </td>
                            @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false))
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn-checkout" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditAtt"
                                            onclick="prefillEdit({{ $att->id }}, '{{ $att->status }}', '{{ $att->note ?? '' }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.attendance.delete', $att->id) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Hapus data kehadiran ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-calendar-times d-block"></i>
                                <div class="fw-bold" style="font-size:1.1rem;">Tidak ada data kehadiran</div>
                                <div>Belum ada data untuk rentang waktu yang dipilih</div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Tab: Log Kamera AI ─────────────────────────────────── --}}
        <div id="tab-kamera" class="tab-content-pane" style="display:none;">
            <div class="table-responsive">
                <table class="att-table">
                    <thead>
                        <tr>
                            <th style="padding-left:24px;">Tangkapan</th>
                            <th>Siapa?</th>
                            <th>Waktu</th>
                            <th>Kamera</th>
                            <th>Status AI</th>
                            <th>Akurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faceLogs as $log)
                        <tr class="{{ $log->status === 'tidak_dikenal' ? 'row-unknown' : '' }}">
                            <td style="padding-left:24px;">
                                @if($log->foto_capture_path)
                                    <img src="{{ asset('storage/'.$log->foto_capture_path) }}"
                                         class="glightbox-trigger rounded-3 shadow-sm"
                                         data-src="{{ asset('storage/'.$log->foto_capture_path) }}"
                                         style="width:42px;height:42px;object-fit:cover;cursor:pointer;">
                                @else
                                    <div class="bg-light rounded-3" style="width:42px;height:42px;"></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold {{ $log->status === 'tidak_dikenal' ? 'text-danger' : '' }}">
                                    {{ $log->status === 'tidak_dikenal' ? '⚠ Wajah Asing' : ($log->child->nama ?? 'Seseorang') }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.82rem;">{{ $log->waktu_deteksi->isoFormat('D MMM') }}</div>
                                <div class="text-muted" style="font-size:.78rem;font-family:monospace;">{{ $log->waktu_deteksi->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size:.75rem;">
                                    <i class="fas fa-camera me-1 text-primary"></i>{{ strtoupper($log->kamera_id) }}
                                </span>
                            </td>
                            <td>
                                @if($log->status === 'check_in')
                                    <span class="status-pill badge-hadir">Check In</span>
                                @elseif($log->status === 'tidak_dikenal')
                                    <span class="status-pill badge-alpa">Asing</span>
                                @else
                                    <span class="status-pill" style="background:#f1f5f9;color:#64748b;">{{ str_replace('_',' ',ucfirst($log->status)) }}</span>
                                @endif
                            </td>
                            <td>
                                @php $conf = $log->confidence_score ?? 0; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:60px;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                        <div style="width:{{ min($conf,100) }}%;height:100%;background:{{ $conf>=75?'#22c55e':($conf>=50?'#f59e0b':'#ef4444') }};border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:.78rem;font-weight:700;">{{ number_format($conf,0) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-robot d-block"></i>
                                <div class="fw-bold" style="font-size:1.1rem;">Belum ada data AI</div>
                                <div>Sistem face recognition belum mendeteksi aktivitas</div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            @if($faceLogs->hasPages())
            <div class="p-3 border-top">
                {{ $faceLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════ MODAL: Check-In Manual ════════════════════════════ --}}
<div class="modal fade" id="modalManual" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-pen-to-square me-2 text-primary"></i>Input Kehadiran Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.attendance.manual') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Anak *</label>
                            <select class="form-select" name="child_id" required>
                                <option value="">Pilih Anak...</option>
                                @foreach($children as $child)
                                    <option value="{{ $child->id }}">{{ $child->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Masuk *</label>
                            <input type="time" class="form-control" name="check_in" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Keluar</label>
                            <input type="time" class="form-control" name="check_out">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="note" rows="2" placeholder="Opsional..."></textarea>
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

{{-- ═══════════ MODAL: Edit Kehadiran ═══════════════════════════ --}}
<div class="modal fade" id="modalEditAtt" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Data Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAttForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" id="editStatus" required>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpa">Alpa</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="note" id="editNote" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-check me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
    // ── Tab switching ──────────────────────────────────────────────
    document.querySelectorAll('#attTabs .btn-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#attTabs .btn-pill').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content-pane').forEach(p => p.style.display = 'none');
            btn.classList.add('active');
            document.getElementById(btn.dataset.target).style.display = '';
        });
    });

    // ── Edit attendance modal ──────────────────────────────────────
    function prefillEdit(id, status, note) {
        const base = '{{ route("admin.attendance.update", ["attendance"=>":id"]) }}';
        document.getElementById('editAttForm').action = base.replace(':id', id);
        document.getElementById('editId').value = id;
        document.getElementById('editStatus').value = status;
        document.getElementById('editNote').value = note;
    }

    // ── GLightbox ─────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        GLightbox({ selector: '.glightbox-trigger' });

        // Auto-refresh hari ini setelah 60 detik
        const today = '{{ date("Y-m-d") }}';
        const selDate = '{{ $date }}';
        if (selDate === today) {
            setTimeout(() => location.reload(), 60000);
        }
    });
</script>
@endpush