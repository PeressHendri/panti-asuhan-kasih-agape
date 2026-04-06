@extends('layouts.app')

@section('title', 'Dashboard Donatur')
@section('header-title', 'Dashboard')

@section('content')
    @push('styles')
        <style>
            :root {
                --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                --card-bg: rgba(255, 255, 255, 0.85);
                --card-border: rgba(255, 255, 255, 0.4);
                --text-dark: #1e293b;
                --text-muted: #64748b;
            }

            .sponsor-banner {
                position: relative;
                padding: 2.5rem 2rem;
                color: #ffffff;
                border-radius: 20px;
                background: var(--primary-gradient);
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
                margin-bottom: 2rem;
            }

            .sponsor-banner::before {
                content: '';
                position: absolute;
                top: -50%; width: 100%; height: 200%;
                left: 50%; opacity: 0.1;
                background: radial-gradient(circle, #fff 10%, transparent 40%);
                transform: translateX(-50%);
                pointer-events: none;
            }

            .sponsor-banner h2 {
                font-size: 1.8rem;
                font-weight: 800;
                margin-bottom: 0.5rem;
                letter-spacing: -0.5px;
            }
            .sponsor-banner p {
                font-size: 1rem;
                font-weight: 300;
                opacity: 0.9;
                max-width: 600px;
                margin: 0;
            }

            .glass-stat-card {
                padding: 1.75rem 1.5rem;
                text-align: left;
                background: var(--card-bg);
                backdrop-filter: blur(10px);
                border: 1px solid var(--card-border);
                border-radius: 20px;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
                display: flex;
                align-items: center;
                gap: 1.25rem;
            }

            .glass-stat-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
            }

            /* Overriding raw colors from backend with modern equivalents */
            .glass-stat-card[data-index="0"] .stat-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
            .glass-stat-card[data-index="1"] .stat-icon { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
            .glass-stat-card[data-index="2"] .stat-icon { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }
            .glass-stat-card[data-index="3"] .stat-icon { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }

            .stat-icon {
                width: 65px;
                height: 65px;
                font-size: 1.5rem;
                color: #fff;
                border-radius: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            }

            .stat-content {
                display: flex;
                flex-direction: column;
            }

            .stat-title {
                font-size: 0.85rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--text-muted);
            }

            .stat-number {
                font-size: 2.2rem;
                font-weight: 800;
                line-height: 1.1;
                color: var(--text-dark);
                margin-top: 2px;
            }

            .nav-card {
                background: #fff;
                border: 1px solid #f1f5f9;
                border-radius: 20px;
                padding: 2rem 1.5rem;
                text-align: center;
                transition: all 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            }

            .nav-card:hover {
                border-color: #cbd5e1;
                box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                transform: translateY(-5px);
            }

            .nav-icon {
                width: 70px;
                height: 70px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                margin-bottom: 1.25rem;
            }

            .nav-card h5 {
                font-weight: 800;
                font-size: 1.15rem;
                color: var(--text-dark);
                margin-bottom: 0.5rem;
            }

            .nav-card-text {
                font-size: 0.9rem;
                color: var(--text-muted);
                margin-bottom: 1.5rem;
                line-height: 1.5;
            }

            .btn-nav {
                margin-top: auto;
                border-radius: 12px;
                font-weight: 700;
                padding: 0.6rem 1.2rem;
                width: 100%;
                transition: all 0.2s ease;
            }

            .btn-nav-primary {
                background: #eff6ff; color: #3b82f6; border: 1px solid transparent;
            }
            .btn-nav-primary:hover { background: #3b82f6; color: #fff; }

            .btn-nav-success {
                background: #f0fdf4; color: #22c55e; border: 1px solid transparent;
            }
            .btn-nav-success:hover { background: #22c55e; color: #fff; }

            .btn-nav-purple {
                background: #f5f3ff; color: #8b5cf6; border: 1px solid transparent;
            }
            .btn-nav-purple:hover { background: #8b5cf6; color: #fff; }
        </style>
    @endpush

    {{-- Banner Sambutan --}}
    <div class="sponsor-banner mb-4">
        <h2>Selamat datang, {{ Auth::user()->name }} 👋</h2>
        <p>Terima kasih atas kemurahan hati Anda. Dukungan Anda membawa harapan, masa depan, dan senyuman bagi anak-anak kami di Panti Asuhan Kasih Agape.</p>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-5">
        @foreach ($statCards as $index => $card)
            <div class="col">
                <div class="glass-stat-card" data-index="{{ $index }}">
                    <div class="stat-icon">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-title">{{ $card['title'] }}</div>
                        <div class="stat-number">{{ $card['count'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Kartu Navigasi --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="nav-card">
                <div class="nav-icon" style="background:#eff6ff; color:#3b82f6;">
                    <i class="fas fa-home"></i>
                </div>
                <h5>Profil Panti</h5>
                <p class="nav-card-text">Kenali lebih dalam tentang visi, misi, dan kontak Panti Asuhan Kasih Agape.</p>
                <a href="{{ route('sponsor.profile.panti') }}" class="btn btn-nav btn-nav-primary">Jelajahi Profil</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="nav-card">
                <div class="nav-icon" style="background:#f0fdf4; color:#22c55e;">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <h5>Data Pengasuh</h5>
                <p class="nav-card-text">Lihat siapa saja yang merawat dan mendidik anak-anak panti secara tulus setiap harinya.</p>
                <a href="{{ route('sponsor.pengasuh') }}" class="btn btn-nav btn-nav-success">Lihat Pengasuh</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="nav-card">
                <div class="nav-icon" style="background:#f5f3ff; color:#8b5cf6;">
                    <i class="fas fa-video"></i>
                </div>
                <h5>Monitoring CCTV</h5>
                <p class="nav-card-text">Pantau aktivitas anak-anak secara transparan dan aman melalui akses live CCTV kami.</p>
                <a href="{{ route('dashboard.cctv') }}" class="btn btn-nav btn-nav-purple">Buka CCTV</a>
            </div>
        </div>
    </div>

@endsection