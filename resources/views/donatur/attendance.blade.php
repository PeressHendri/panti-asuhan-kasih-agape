@extends('layouts.app')

@section('title', 'Data Kehadiran Anak')
@section('header-title', 'Kehadiran Anak')

@push('styles')
<style>
    :root { --att-green: #22c55e; --att-yellow: #f59e0b; --att-blue: #3b82f6; --att-red: #ef4444; }

    .stat-card {
        border-radius: 16px; border: 1px solid #e2e8f0;
        background: #fff; padding: 20px 24px;
        transition: transform .2s, box-shadow .2s;
        position: relative; overflow: hidden;
    }
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
    .stat-card.green::before  { background: var(--att-green); }
    .stat-card.yellow::before { background: var(--att-yellow); }
    .stat-card.blue::before   { background: var(--att-blue); }
    .stat-card.red::before    { background: var(--att-red); }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.08); }

    .stat-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem; }
    .stat-icon.green  { background:#f0fdf4;color:var(--att-green); }
    .stat-icon.yellow { background:#fffbeb;color:var(--att-yellow); }
    .stat-icon.blue   { background:#eff6ff;color:var(--att-blue); }
    .stat-icon.red    { background:#fef2f2;color:var(--att-red); }
    .stat-label { font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.8px;font-weight:700; }
    .stat-value { font-size:1.9rem;font-weight:800;line-height:1;margin-top:4px;color:#0f172a; }

    .main-card { background:#fff;border-radius:20px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .main-card-header { padding:18px 24px;border-bottom:1px solid #f1f5f9;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between; }

    .att-table { width:100%;border-collapse:collapse; }
    .att-table thead th { padding:12px 16px;font-size:.72rem;text-transform:uppercase;letter-spacing:.7px;font-weight:700;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #f1f5f9; }
    .att-table tbody tr { border-bottom:1px solid #f8fafc;transition:background .15s; }
    .att-table tbody tr:hover { background:#f8fafc; }
    .att-table td { padding:13px 16px;font-size:.88rem;color:#334155;vertical-align:middle; }

    .child-avatar { width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem; }
    .time-badge { font-size:.8rem;font-weight:700;background:#f1f5f9;color:#475569;padding:4px 10px;border-radius:8px;font-family:monospace; }
    .status-pill { padding:3px 12px;border-radius:50px;font-size:.75rem;font-weight:700; }
    .badge-hadir  { background:#dcfce7;color:#15803d; }
    .badge-sakit  { background:#fef9c3;color:#a16207; }
    .badge-izin   { background:#e0f2fe;color:#0369a1; }
    .badge-alpa   { background:#fee2e2;color:#b91c1c; }

    .readonly-notice {
        background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 12px;
        padding: 12px 16px; display: flex; align-items: center; gap: 10px;
    }

    .empty-state { padding:60px 20px;text-align:center;color:#94a3b8; }
    .empty-state i { font-size:3.5rem;margin-bottom:16px;opacity:.4; }

    .filter-bar input { border-radius:10px;border:1.5px solid #e2e8f0;padding:7px 14px;font-size:.83rem;font-weight:600;color:#334155; }
    .filter-bar input:focus { border-color:var(--att-blue);outline:none; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h5 class="fw-bold mb-1" style="color:#0f172a;">Rekap Kehadiran Anak Asuh</h5>
            <p class="text-muted mb-0" style="font-size:.83rem;">Data kehadiran anak yang dapat Anda pantau sebagai sponsor</p>
        </div>
        <div class="filter-bar">
            <form action="{{ route('sponsor.attendance') }}" method="GET" class="d-flex gap-2">
                <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}"
                       onchange="this.form.submit()">
            </form>
        </div>
    </div>

    {{-- Stat Cards --}}
    @php
        $allAtt = $attendances->getCollection();
        $hadirCount  = $allAtt->where('status','hadir')->count();
        $sakitCount  = $allAtt->where('status','sakit')->count();
        $izinCount   = $allAtt->where('status','izin')->count();
        $alpaCount   = $allAtt->where('status','alpa')->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card green">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="stat-label">Hadir</div><div class="stat-value text-success">{{ $hadirCount }}</div></div>
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card yellow">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="stat-label">Sakit</div><div class="stat-value" style="color:var(--att-yellow);">{{ $sakitCount }}</div></div>
                    <div class="stat-icon yellow"><i class="fas fa-notes-medical"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="stat-label">Izin</div><div class="stat-value text-primary">{{ $izinCount }}</div></div>
                    <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card red">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="stat-label">Alpa</div><div class="stat-value text-danger">{{ $alpaCount }}</div></div>
                    <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Readonly Notice --}}
    <div class="readonly-notice mb-4">
        <i class="fas fa-eye text-primary fa-lg"></i>
        <div>
            <div class="fw-bold" style="color:#1d4ed8;font-size:.88rem;">Mode Hanya Lihat (Sponsor)</div>
            <div style="font-size:.8rem;color:#475569;">Anda dapat memantau data kehadiran anak asuh. Untuk pertanyaan lebih lanjut, hubungi pihak pengasuh.</div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="main-card">
        <div class="main-card-header">
            <div class="fw-bold" style="color:#0f172a;">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>Data Kehadiran Anak
            </div>
            <span class="badge bg-primary rounded-pill px-3">{{ $attendances->total() }} Record</span>
        </div>
        <div class="table-responsive">
            <table class="att-table">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">Nama Anak</th>
                        <th>Tanggal</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Keterangan</th>
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
                                <div class="fw-bold" style="color:#0f172a;">{{ $att->child->nama ?? 'Tidak diketahui' }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:.85rem;">
                                {{ \Carbon\Carbon::parse($att->date)->isoFormat('D MMM YYYY') }}
                            </div>
                        </td>
                        <td>
                            @if($att->check_in)
                                <span class="time-badge">{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_out)
                                <span class="time-badge">{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                            @else
                                <span class="text-muted small">Belum checkout</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_in && $att->check_out)
                                @php
                                    $dur = \Carbon\Carbon::parse($att->check_in)->diffInMinutes(\Carbon\Carbon::parse($att->check_out));
                                    $h = intdiv($dur, 60); $m = $dur % 60;
                                @endphp
                                <span class="time-badge">{{ $h > 0 ? $h.'j '.$m.'m' : $m.'m' }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @php $cls = ['hadir'=>'badge-hadir','sakit'=>'badge-sakit','izin'=>'badge-izin','alpa'=>'badge-alpa']; @endphp
                            <span class="status-pill {{ $cls[$att->status] ?? 'badge-alpa' }}">{{ ucfirst($att->status) }}</span>
                        </td>
                        <td><span class="text-muted" style="font-size:.82rem;">{{ $att->note ?? '-' }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-calendar-times d-block"></i>
                            <div class="fw-bold" style="font-size:1.1rem;">Tidak ada data kehadiran</div>
                            <div>Belum ada rekap kehadiran untuk tanggal yang dipilih</div>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
        <div class="p-3 border-top">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection