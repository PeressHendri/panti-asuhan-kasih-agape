@extends('layouts.app')

@section('title', 'Kehadiran Anak')

@section('content')
<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary">
                    <h5 class="mb-0 dashboard-title">Kehadiran Hari Ini - {{ now()->format('d F Y') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Check In Form -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="{{ route('admin.attendance.check-in') }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <select name="child_id" class="form-control select2 dashboard-text" required>
                                        <option value="">Pilih Anak</option>
                                        @foreach($children as $child)
                                        <option value="{{ $child->id }}">{{ $child->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success dashboard-label">
                                            <i class="fas fa-sign-in-alt"></i> Check In
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th class="dashboard-label">No</th>
                                    <th class="dashboard-label">Nama Anak</th>
                                    <th class="dashboard-label">Check In</th>
                                    <th class="dashboard-label">Check Out</th>
                                    <th class="dashboard-label">Status</th>
                                    <th class="dashboard-label">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $key => $attendance)
                                <tr>
                                    <td class="dashboard-text">{{ $key+1 }}</td>
                                    <td class="dashboard-text">{{ $attendance->child->nama ?? '-' }}</td>
                                    <td class="dashboard-text">{{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-' }}</td>
                                    <td class="dashboard-text">
                                        @if($attendance->check_out)
                                            {{ $attendance->check_out->format('H:i:s') }}
                                        @else
                                            <span class="dashboard-label">Belum check out</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $attendance->status == 'hadir' ? 'var(--badge-success-bg)' : 'var(--badge-danger-bg)' }}; color: var(--badge-text-color);">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(!$attendance->check_out)
                                        <form action="{{ route('admin.attendance.check-out', $attendance->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning dashboard-label">
                                                <i class="fas fa-sign-out-alt"></i> Check Out
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.dashboard-label, .dashboard-title {
    color: var(--heading-color) !important;
    font-weight: 600;
}
.dashboard-text {
    color: var(--text-color) !important;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
@endsection