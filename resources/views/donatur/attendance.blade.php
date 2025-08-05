@extends('layouts.app')

@section('title', 'Kehadiran Anak - Donatur')
@section('header-title', 'Kehadiran Anak')

@section('content')
<div class="container-fluid p-4 text-light">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Data Kehadiran Anak</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Anak</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') : '-' }}</td>
                            <td>{{ $attendance->child->nama ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $attendance->status == 'hadir' ? 'success' : ($attendance->status == 'sakit' ? 'warning' : 'info') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
                            <td>{{ $attendance->note ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data kehadiran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 