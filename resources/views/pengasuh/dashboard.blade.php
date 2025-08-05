@extends('layouts.app')

@section('title', 'Dashboard Pengasuh')
@section('header-title', 'Dashboard Pengasuh')

{{-- Menambahkan CSS khusus untuk Info Card, sama seperti di dashboard admin --}}
@push('styles')
<style>
    .info-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 2px_10px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-align: center;
        border: 1px solid #e9ecef;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .info-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem auto;
        color: #ffffff;
    }

    .info-card-icon i {
        font-size: 1.75rem;
    }

    .info-card-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .info-card-text {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
@php
    // Data untuk info cards, membuat kode lebih bersih dan mudah dikelola
    $statCards = [
        ['count' => $stats['total_pengasuh'] ?? 0, 'title' => 'Total Pengasuh', 'icon' => 'fa-users', 'color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        ['count' => $stats['total_children'] ?? 0, 'title' => 'Total Anak', 'icon' => 'fa-child', 'color' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'],
        ['count' => $stats['total_laki'] ?? 0, 'title' => 'Laki-laki', 'icon' => 'fa-mars', 'color' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'],
        ['count' => $stats['total_perempuan'] ?? 0, 'title' => 'Perempuan', 'icon' => 'fa-venus', 'color' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
    ];
@endphp

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
    @foreach ($statCards as $card)
        <div class="col">
            <div class="info-card h-100">
                <div class="info-card-icon" style="background: {{ $card['color'] }};">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <h3 class="info-card-title">{{ $card['count'] }}</h3>
                <p class="info-card-text">{{ $card['title'] }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Aktivitas Kehadiran Terkini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Anak</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_activities as $activity)
                                <tr>
                                    <td class="align-middle">{{ $activity->child->name }}</td>
                                    <td class="align-middle">
                                        @php
                                            $statusClass = 'bg-secondary';
                                            if ($activity->status == 'hadir') $statusClass = 'bg-success';
                                            elseif ($activity->status == 'sakit') $statusClass = 'bg-warning text-dark';
                                            elseif ($activity->status == 'izin') $statusClass = 'bg-info text-dark';
                                            elseif ($activity->status == 'alpa') $statusClass = 'bg-danger';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($activity->status) }}</span>
                                    </td>
                                    <td class="align-middle">{{ $activity->created_at->format('H:i') }}</td>
                                    <td class="align-middle">{{ $activity->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada aktivitas kehadiran hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Aksi Cepat</h5>
            </div>
            <div class="card-body d-grid gap-3">
                <a href="{{ route('pengasuh.attendance') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-clipboard-check me-2"></i>Catat Kehadiran
                </a>
                <a href="{{ route('pengasuh.cctv') }}" class="btn btn-dark btn-lg">
                    <i class="fas fa-video me-2"></i>Monitoring CCTV
                </a>
                <a href="{{ route(auth()->user()->role . '.profile.panti') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-users me-2"></i>Lihat Semua Data Anak
                </a>
            </div>
        </div>
    </div>
</div>
@endsection