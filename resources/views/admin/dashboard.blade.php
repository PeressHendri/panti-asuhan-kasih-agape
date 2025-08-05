@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('header-title', 'Dashboard Admin')

{{-- Menambahkan CSS khusus untuk halaman ini --}}
@push('styles')
<style>
    .info-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
    }

    .card-header {
        background-color: #f8f9fa;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
@php
    // Mendefinisikan data untuk stats card dalam sebuah array agar kode lebih bersih
    $statCards = [
        ['count' => $stats['total_children'] ?? 0, 'title' => 'Total Anak Panti', 'icon' => 'fa-child', 'color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        ['count' => $stats['total_users'] ?? 0, 'title' => 'Total Pengguna', 'icon' => 'fa-users', 'color' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'],
        ['count' => $stats['total_admin'] ?? 0, 'title' => 'Admin', 'icon' => 'fa-user-shield', 'color' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
        ['count' => $stats['total_pengasuh'] ?? 0, 'title' => 'Pengasuh', 'icon' => 'fa-user-nurse', 'color' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'],
        ['count' => $stats['total_donatur'] ?? 0, 'title' => 'Donatur', 'icon' => 'fa-hand-holding-heart', 'color' => 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)'],
    ];
@endphp

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-4 mb-4">
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
                Aktivitas Terbaru
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aktivitas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $log->user->name ?? 'Tidak diketahui' }}</td>
                                    <td>{{ $log->activity }}</td>
                                    <td><span class="badge bg-success">{{ $log->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada aktivitas tercatat.</td>
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
                Informasi Sistem
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Status Server
                        <span class="badge bg-success">Online</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Status CCTV
                        <span class="badge bg-success">Aktif</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Database
                        <span class="badge bg-success">Terhubung</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Backup Terakhir
                        <span class="text-muted small">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection