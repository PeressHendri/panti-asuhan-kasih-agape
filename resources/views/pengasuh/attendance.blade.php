@extends('layouts.app')

@section('title', 'Data Kehadiran')
@section('header-title', 'Data Kehadiran')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row gy-3 align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0">Kehadiran Anak</h5>
            </div>
            <div class="col-md-4">
                <form method="GET" action="{{ route('pengasuh.attendance.index') }}">
                    <div class="input-group">
                        <input type="date" class="form-control" name="date" value="{{ request('date', now()->format('Y-m-d')) }}">
                        <button class="btn btn-outline-secondary" type="submit">Filter</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                    <i class="fas fa-plus me-2"></i>Tambah Kehadiran
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Nama Anak</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                    <tr>
                        <td class="align-middle">{{ $attendance->child->nama ?? 'Anak Dihapus' }}</td>
                        <td class="align-middle">
                            @php
                                $statusClass = 'bg-secondary';
                                if ($attendance->status == 'hadir') $statusClass = 'bg-success';
                                elseif ($attendance->status == 'sakit') $statusClass = 'bg-warning text-dark';
                                elseif ($attendance->status == 'izin') $statusClass = 'bg-info text-dark';
                                elseif ($attendance->status == 'alpa') $statusClass = 'bg-danger';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($attendance->status) }}</span>
                        </td>
                        <td class="align-middle">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                        <td class="align-middle">
                            @if($attendance->check_out)
                                {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                            @elseif($attendance->status == 'hadir')
                                <form action="{{ route('pengasuh.attendance.check-out', $attendance->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Check Out</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                        <td class="align-middle">{{ $attendance->note ?? '-' }}</td>
                        <td class="text-center align-middle">
                            <button class="btn btn-sm btn-warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editAttendanceModal"
                                    data-id="{{ $attendance->id }}"
                                    data-child-name="{{ $attendance->child->nama ?? 'Anak Dihapus' }}"
                                    data-status="{{ $attendance->status }}"
                                    data-note="{{ $attendance->note }}"
                                    data-url="{{ route('pengasuh.attendance.update', $attendance->id) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada data kehadiran untuk tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-labelledby="addAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAttendanceModalLabel">Tambah Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pengasuh.attendance.check-in') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_child_id" class="form-label">Nama Anak</label>
                        <select class="form-select" name="child_id" id="add_child_id" required>
                            <option value="" disabled selected>Pilih Anak...</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}">{{ $child->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="add_status" required>
                            <option value="hadir">Hadir</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="alpa">Alpa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_note" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" name="note" id="add_note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAttendanceModalLabel">Edit Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAttendanceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Anak</label>
                        <input type="text" id="edit_child_name" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="hadir">Hadir</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                             <option value="alpa">Alpa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_note" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" name="note" id="edit_note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editAttendanceModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        // Tombol yang memicu modal
        const button = event.relatedTarget;

        // Ambil data dari atribut data-*
        const url = button.getAttribute('data-url');
        const childName = button.getAttribute('data-child-name');
        const status = button.getAttribute('data-status');
        const note = button.getAttribute('data-note');

        // Dapatkan elemen form di dalam modal
        const form = editModal.querySelector('#editAttendanceForm');
        const childNameInput = editModal.querySelector('#edit_child_name');
        const statusSelect = editModal.querySelector('#edit_status');
        const noteTextarea = editModal.querySelector('#edit_note');
        
        // Isi form dengan data yang didapat
        form.setAttribute('action', url);
        childNameInput.value = childName;
        statusSelect.value = status;
        noteTextarea.value = note;
    });
});
</script>
@endpush