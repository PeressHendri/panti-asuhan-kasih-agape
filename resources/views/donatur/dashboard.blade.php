@extends('layouts.app')

@section('title', 'Dashboard Donatur')
@section('header-title', 'Dashboard Donatur')

@section('content')
    @push('styles')
        <style>
            .welcome-banner {
                padding: 0.75rem 0.5rem;
                color: #ffffff;
                border-radius: 0.75rem;
                background: linear-gradient(120deg, #2c3e50, #4ca1af);
            }

            .welcome-banner h2 {
                font-size: 1.65rem;
                margin-bottom: 0rem;
            }

            .stat-card {
                padding: 1.5rem;
                text-align: center;
                background-color: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 0.75rem;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .stat-card .stat-icon {
                width: 60px;
                height: 60px;
                margin: 0 auto 1rem auto;
                font-size: 1.75rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f4f7f6;
            }

            .stat-card .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
            }
        </style>
    @endpush
    {{-- Banner Sambutan --}}
    <div class="welcome-banner p-4 p-md-5 mb-4 shadow-sm">
        <h2 class="display-6">Selamat datang, {{ Auth::user()->name }}!</h2>
        <p class="lead">Terima kasih atas kemurahan hati Anda. Dukungan Anda membawa harapan dan senyuman bagi anak-anak
            kami di Panti Asuhan Kasih Agape.</p>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
        @foreach ($statCards as $card)
            <div class="col">
                <div class="stat-card h-100">
                    <div class="stat-icon" style="background: {{ $card['color'] }}; color: white; border-radius: 50%;">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                    <div class="stat-number">{{ $card['count'] }}</div>
                    <div class="text-muted">{{ $card['title'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Kartu Navigasi --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Profil Panti</h5>
                    <p class="card-text">Kenali lebih dalam tentang visi, misi, dan kontak Panti Asuhan Kasih Agape.</p>
                    <a href="{{ route('donatur.profile.panti') }}" class="btn btn-outline-primary">Lihat Profil Panti</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-user-nurse fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Data Pengasuh</h5>
                    <p class="card-text">Lihat siapa saja yang merawat dan mendidik anak-anak panti setiap harinya.</p>
                    <a href="{{ route('donatur.pengasuh') }}" class="btn btn-outline-success">Lihat Data Pengasuh</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-video fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Monitoring CCTV</h5>
                    <p class="card-text">Pantau aktivitas anak-anak secara transparan melalui akses CCTV.</p>
                    <a href="{{ route('donatur.cctv') }}" class="btn btn-outline-secondary">Lihat CCTV</a>
                </div>
            </div>
        </div>
    </div>

@endsection