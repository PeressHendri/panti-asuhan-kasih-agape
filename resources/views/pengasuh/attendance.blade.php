@extends('layouts.app')

@section('title', 'Data Kehadiran')
@section('header-title', 'Kehadiran Anak')

@push('styles')
<style>
    :root {
        --att-blue:   #3b82f6;
        --att-green:  #22c55e;
        --att-yellow: #f59e0b;
        --att-red:    #ef4444;
    }

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

    .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .stat-icon.blue   { background: #eff6ff; color: var(--att-blue); }
    .stat-icon.green  { background: #f0fdf4; color: var(--att-green); }
    .stat-icon.yellow { background: #fffbeb; color: var(--att-yellow); }
    .stat-icon.red    { background: #fef2f2; color: var(--att-red); }
    .stat-label { font-size: .7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .8px; font-weight: 700; }
    .stat-value { font-size: 1.9rem; font-weight: 800; line-height: 1; margin-top: 4px; color: #0f172a; }

    .main-card { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .main-card-header { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; }

    .quick-form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .quick-form select, .quick-form input {
        border-radius: 10px; border: 1.5px solid #e2e8f0;
        padding: 7px 14px; font-size: .83rem; font-weight: 600; color: #334155;
    }
    .quick-form select:focus, .quick-form input:focus {
        border-color: var(--att-blue); box-shadow: 0 0 0 3px rgba(59,130,246,.1); outline: none;
    }
    .btn-checkin { background: var(--att-green); color: #fff; border-radius: 10px; border: none; padding: 7px 18px; font-weight: 700; font-size: .83rem; transition: all .2s; }
    .btn-checkin:hover { background: #16a34a; transform: translateY(-1px); }

    .att-table { width: 100%; border-collapse: collapse; }
    .att-table thead th { padding: 12px 16px; font-size: .72rem; text-transform: uppercase; letter-spacing: .7px; font-weight: 700; color: #94a3b8; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
    .att-table tbody tr { border-bottom: 1px solid #f8fafc; transition: background .15s; }
    .att-table tbody tr:hover { background: #f8fafc; }
    .att-table td { padding: 13px 16px; font-size: .88rem; color: #334155; vertical-align: middle; }

    .child-avatar { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .85rem; }

    .time-badge { font-size: .8rem; font-weight: 700; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-family: monospace; }
    .time-badge.active-out { background: #fef9c3; color: #b45309; }

    .status-pill { padding: 3px 12px; border-radius: 50px; font-size: .75rem; font-weight: 700; }
    .badge-hadir  { background:#dcfce7; color:#15803d; }
    .badge-sakit  { background:#fef9c3; color:#a16207; }
    .badge-izin   { background:#e0f2fe; color:#0369a1; }
    .badge-alpa   { background:#fee2e2; color:#b91c1c; }

    .btn-checkout { background: #fff7ed; border: 1.5px solid #fed7aa; color: #c2410c; border-radius: 8px; padding: 4px 12px; font-size: .78rem; font-weight: 700; transition: all .2s; cursor: pointer; }
    .btn-checkout:hover { background: #ffedd5; }
    .btn-edit { background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; border-radius: 8px; padding: 4px 10px; font-size: .78rem; transition: all .2s; cursor: pointer; }
    .btn-edit:hover { background: #dbeafe; }

    .empty-state { padding: 60px 20px; text-align: center; color: #94a3b8; }
    .empty-state i { font-size: 3.5rem; margin-bottom: 16px; opacity: .4; }

    .date-filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .date-filter-bar input { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 7px 14px; font-size: .83rem; font-weight: 600; color: #334155; }
    .date-filter-bar input:focus { border-color: var(--att-blue); outline:none; }

    .modal-content { border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,.15); }
    .modal-header  { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
    .modal-body    { padding: 24px; }
    .modal-footer  { border-top: 1px solid #f1f5f9; padding: 16px 24px; }
    .form-label  { font-size: .83rem; font-weight: 700; color: #475569; margin-bottom: 6px; }
    .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 9px 14px; font-size: .88rem; }
    .form-control:focus, .form-select:focus { border-color: var(--att-blue); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header & Filter ──────────────────────────────────────── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h5 class="fw-bold mb-1" style="color:#0f172a;">
                Kehadiran Anak — {{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM YYYY') }}
            </h5>
            <p class="text-muted mb-0" style="font-size:.83rem;">Data absensi harian anak asuh secara real-time</p>
        </div>
        <div class="date-filter-bar">
            <form action="{{ route('pengasuh.attendance') }}" method="GET" class="d-flex gap-2 align-items-center">
                <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}"
                       onchange="this.form.submit()">
            </form>
        </div>
    </div>

    {{-- ── Stats ───────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        @php
            $hadir = $attendances->where('status','hadir')->count();
            $sakit = $attendances->where('status','sakit')->count();
            $izin  = $attendances->where('status','izin')->count();
            $alpa  = $attendances->where('status','alpa')->count();
        @endphp
        <div class="col-6 col-md-3">
            <div class="stat-card green">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Hadir</div>
                        <div class="stat-value text-success">{{ $hadir }}</div>
                    </div>
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card yellow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Sakit</div>
                        <div class="stat-value" style="color:var(--att-yellow);">{{ $sakit }}</div>
                    </div>
                    <div class="stat-icon yellow"><i class="fas fa-notes-medical"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Izin</div>
                        <div class="stat-value text-primary">{{ $izin }}</div>
                    </div>
                    <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card red">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Alpa</div>
                        <div class="stat-value text-danger">{{ $alpa }}</div>
                    </div>
                    <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Card ───────────────────────────────────────────── --}}
    <div class="main-card">
        <div class="main-card-header">
            <div class="fw-bold" style="color:#0f172a;">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>Daftar Kehadiran
            </div>

            <div class="d-flex gap-2 flex-wrap">
                @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false))
                {{-- Quick Check-In --}}
                <form action="{{ route('pengasuh.attendance.check-in') }}" method="POST" class="quick-form">
                    @csrf
                    <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">
                    <select name="child_id" required style="min-width:160px;">
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
                </form>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                        data-bs-toggle="modal" data-bs-target="#modalManual"
                        style="font-weight:700;font-size:.8rem;">
                    <i class="fas fa-pen me-1"></i>Input Manual
                </button>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="att-table">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">Anak</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
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
                            @if($att->check_in)
                                <span class="time-badge">{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_out)
                                <span class="time-badge">{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                            @elseif($att->status === 'hadir' && $att->check_in)
                                @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false))
                                    <form action="{{ route('pengasuh.attendance.check-out', $att->id) }}" method="POST" class="d-inline m-0">
                                        @csrf
                                        <button type="submit" class="btn-checkout">
                                            <i class="fas fa-sign-out-alt me-1"></i>Check-Out
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_in && $att->check_out)
                                @php
                                    $dur = \Carbon\Carbon::parse($att->check_in)->diffInMinutes(\Carbon\Carbon::parse($att->check_out));
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
                            @endphp
                            <span class="status-pill {{ $cls[$att->status] ?? 'badge-alpa' }}">
                                {{ ucfirst($att->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:.82rem;">{{ $att->note ?? '-' }}</span>
                        </td>
                        <td>
                            @if(\Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false))
                                <button class="btn-edit"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                                        onclick="prefillEdit({{ $att->id }}, '{{ $att->status }}', '{{ $att->note ?? '' }}', '{{ $att->child->nama ?? '' }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-calendar-times d-block"></i>
                            <div class="fw-bold" style="font-size:1.1rem;">Belum ada data kehadiran hari ini</div>
                            <div>Gunakan form Check-In di atas untuk menambahkan kehadiran</div>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════ MODAL: Input Manual ══════════════════════════════ --}}
<div class="modal fade" id="modalManual" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-pen-to-square me-2 text-primary"></i>Input Kehadiran Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pengasuh.attendance.manual') }}" method="POST">
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

{{-- ═══════════ MODAL: Edit ═══════════════════════════════════════ --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Nama Anak</label>
                        <input type="text" class="form-control bg-light" id="editName" disabled>
                    </div>
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
<script>
function prefillEdit(id, status, note, name) {
    document.getElementById('editForm').action = '{{ route("pengasuh.attendance.update") }}';
    document.getElementById('editId').value = id;
    document.getElementById('editStatus').value = status;
    document.getElementById('editNote').value = note || '';
    document.getElementById('editName').value = name || '';
}
</script>
@endpush