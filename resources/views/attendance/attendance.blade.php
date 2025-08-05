@extends('layouts.app')

@section('title', 'Data Kehadiran')
@section('header-title', 'Data Kehadiran')

@section('content')
<div class="container-fluid py-3">
    <div class="card shadow-sm rounded-4 mb-4" style="border: none;">
        <div class="card-header" style="background-color: var(--card-header-bg); border: none; padding-bottom: 0.5rem;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="color: var(--heading-color);">Kehadiran Hari Ini - {{ now()->format('d F Y') }}</h5>
                @if (auth()->user()->role === 'admin' || auth()->user()->role === 'pengasuh')
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addAttendanceModal"
                        style="color: var(--text-color); background-color: var(--btn-primary-bg);">
                        <i class="fas fa-plus me-2"></i>Tambah Kehadiran
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body rounded-bottom-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead style="background-color: var(--thead-bg);">
                        <tr>
                            <th style="color: var(--heading-color);">Nama Anak</th>
                            <th style="color: var(--heading-color);">Status</th>
                            <th style="color: var(--heading-color);">Check In</th>
                            <th style="color: var(--heading-color);">Check Out</th>
                            <th style="color: var(--heading-color);">Keterangan</th>
                            @if (auth()->user()->role === 'admin' || auth()->user()->role === 'pengasuh')
                                <th style="color: var(--heading-color);">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td style="color: var(--text-color);">{{ $attendance->child->nama ?? 'Tidak Diketahui' }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $attendance->status == 'hadir' ? 'var(--badge-success-bg)' : ($attendance->status == 'sakit' ? 'var(--badge-warning-bg)' : 'var(--badge-info-bg)') }}; color: var(--badge-text-color);">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td style="color: var(--text-color);">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                                <td style="color: var(--text-color);">
                                    @if($attendance->check_out)
                                        {{ $attendance->check_out->format('H:i') }}
                                    @else
                                        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'pengasuh')
                                            <form action="{{ route(auth()->user()->role . '.attendance.check-out', $attendance->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" style="color: var(--text-color); border-color: var(--btn-primary-bg);">Check Out</button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>
                                <td style="color: var(--text-color);">{{ $attendance->note ?? '-' }}</td>
                                @if (auth()->user()->role === 'admin' || auth()->user()->role === 'pengasuh')
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAttendanceModal"
                                            onclick="editAttendance({{ $attendance->id }}, '{{ $attendance->status }}', '{{ $attendance->note ?? '' }}')"
                                            style="color: var(--text-color); background-color: var(--btn-warning-bg);">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role === 'admin' || auth()->user()->role === 'pengasuh' ? 6 : 5 }}" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-calendar-times fa-3x mb-3 text-secondary" style="color: var(--muted-text-color); opacity: 0.5;"></i>
                                        <div class="fs-5 mb-1" style="color: var(--text-color);">Belum ada data kehadiran hari ini</div>
                                        <div style="color: var(--muted-text-color);">Silakan tambahkan data kehadiran dengan tombol di kanan atas</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kehadiran -->
<div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-labelledby="addAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAttendanceModalLabel" style="color: var(--heading-color);">Tambah Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route(auth()->user()->role . '.attendance.check-in') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-color);">Nama Anak</label>
                        <select class="form-select" name="child_id" required>
                            <option value="">Pilih Anak</option>
                            @foreach($children as $child)
                            <option value="{{ $child->id }}">{{ $child->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-color);">Tanggal</label>
                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-color);">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="hadir">Hadir</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-color);">Keterangan (Opsional)</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="color: var(--text-color); background-color: var(--btn-secondary-bg);">Tutup</button>
                    <button type="submit" class="btn btn-primary" style="color: var(--text-color); background-color: var(--btn-primary-bg);">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kehadiran -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAttendanceModalLabel" style="color: var(--heading-color);">Edit Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAttendanceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="editAttendanceId">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-color);">Status</label>
                        <select class="form-select" name="status" id="editStatus" required>
                            <option value="hadir">Hadir</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-color);">Keterangan (Opsional)</label>
                        <textarea class="form-control" name="note" id="editNote" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="color: var(--text-color); background-color: var(--btn-secondary-bg);">Tutup</button>
                    <button type="submit" class="btn btn-primary" style="color: var(--text-color); background-color: var(--btn-primary-bg);">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <style>
        .card.shadow-sm {
            border-radius: 1.25rem !important;
        }
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }
        .table td, .table th {
            text-align: center;
        }
        .btn-primary.rounded-pill {
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .fa-calendar-times {
            opacity: 0.5;
        }
    </style>
    <script>
        function editAttendance(id, status, note) {
            $('#editAttendanceForm').attr('action', '{{ route(auth()->user()->role . '.attendance.update', ['attendance' => ':id']) }}'.replace(':id', id));
            $('#editAttendanceId').val(id);
            $('#editStatus').val(status);
            $('#editNote').val(note || '');
        }
    </script>
@endpush