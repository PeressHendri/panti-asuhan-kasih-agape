@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('header-title', 'Dashboard Admin')

{{-- Menambahkan CSS khusus untuk halaman ini --}}
@push('styles')
    <style>
        .info-card { background: #fff; border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 1.5rem; transition: transform 0.2s ease, box-shadow 0.2s ease; text-align: center; border: 1px solid #e9ecef; }
        .info-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .info-card-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: #fff; }
        .info-card-icon i { font-size: 1.75rem; }
        .info-card-title { font-size: 2rem; font-weight: 700; color: #2c3e50; }
        .info-card-text { font-size: 0.9rem; color: #6c757d; margin-bottom: 0; }
        .card-header { background: #f8f9fa; font-weight: bold; }
        .alert-unknown { background: linear-gradient(135deg,#ff6b6b,#ee5a24); color:#fff; border:0; border-radius:12px; }
    </style>
@endpush

@section('content')
@php
    $statCards = [
        ['count' => $stats['total_children'] ?? 0, 'title' => 'Total Anak Panti',    'icon' => 'fa-child',             'color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        ['count' => $stats['total_users']    ?? 0, 'title' => 'Total Pengguna',      'icon' => 'fa-users',             'color' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'],
        ['count' => $stats['total_admin']    ?? 0, 'title' => 'Admin',               'icon' => 'fa-user-shield',       'color' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
        ['count' => $stats['total_pengasuh'] ?? 0, 'title' => 'Pengasuh',            'icon' => 'fa-user-nurse',        'color' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'],
        ['count' => $stats['total_sponsor']  ?? 0, 'title' => 'Sponsor',             'icon' => 'fa-hand-holding-heart','color' => 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)'],
    ];
@endphp

    {{-- Alert Wajah Asing --}}
    @if(($unknownToday ?? 0) > 0)
    <div class="alert alert-unknown d-flex align-items-center gap-3 mb-4 shadow">
        <i class="fas fa-exclamation-triangle fa-2x"></i>
        <div>
            <strong>Peringatan Keamanan!</strong> Hari ini terdeteksi <strong>{{ $unknownToday }}</strong> wajah tidak dikenal oleh sistem CCTV.
            <a href="{{ route('admin.attendance') }}" class="text-white text-decoration-underline ms-2">Lihat Log &rarr;</a>
        </div>
    </div>
    @endif

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

    {{-- Grafik Kehadiran --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-bar text-primary me-2"></i>Tren Kehadiran 7 Hari Terakhir</span>
                </div>
                <div class="card-body"><canvas id="chartKehadiran" height="100"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header"><i class="fas fa-chart-pie text-success me-2"></i>Kehadiran Hari Ini</div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div style="position:relative; width:180px; height:180px;">
                        <canvas id="chartDonut"></canvas>
                        @if(($todayHadir ?? 0) == 0 && ($todayAlpa ?? 0) == 0)
                        <div id="donutEmpty" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                            <i class="fas fa-moon text-secondary" style="font-size:2rem;"></i>
                            <div class="text-muted small mt-1" style="font-size:0.75rem;">Belum Ada<br>Data Hari Ini</div>
                        </div>
                        @endif
                    </div>
                    <div class="mt-3 text-center">
                        <span class="badge bg-success me-2">Hadir: {{ $todayHadir ?? 0 }}</span>
                        <span class="badge bg-danger">Alpa: {{ $todayAlpa ?? 0 }}</span>
                        <div class="text-muted small mt-1">Total Anak: {{ $totalAnak ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">Aktivitas Terbaru</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($activities as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $log->user->name ?? 'Tidak diketahui' }}</td>
                                        <td>{{ $log->activity }}</td>
                                        <td><span class="badge bg-success">{{ $log->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada aktivitas tercatat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Informasi Sistem</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Status Server Web <span class="badge bg-success">Online</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            AI CCTV (Raspberry Pi)
                            @if($isPiOnline)
                                <span class="badge bg-success">Online</span>
                            @else
                                <span class="badge bg-danger">Offline</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Database <span class="badge bg-success">Terhubung</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Wajah Asing Hari Ini <span class="badge {{ ($unknownToday ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }}">{{ $unknownToday ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Waktu Sekarang <span class="text-muted small">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 pt-3 border-top">
                        <form action="{{ route('admin.deploy') }}" method="POST" onsubmit="return confirm('Tarik pembaruan terbaru dari GitHub sekarang? Ini akan memperbarui kode sistem ke versi terbaru.');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none; padding: 10px;">
                                <i class="fas fa-cloud-download-alt"></i> Tarik Pembaruan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Bar Chart - Tren Kehadiran
    const ctx1 = document.getElementById('chartKehadiran').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                { label: 'Hadir', data: {!! json_encode($chartHadir) !!}, backgroundColor: 'rgba(40,167,69,0.75)', borderRadius: 6 },
                { label: 'Alpa', data: {!! json_encode($chartAlpa) !!}, backgroundColor: 'rgba(220,53,69,0.65)', borderRadius: 6 },
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, precision: 0 } } }
    });

    // Donut - Hari ini
    const todayHadir = {{ $todayHadir ?? 0 }};
    const todayAlpa  = {{ $todayAlpa ?? 0 }};
    const totalAnak  = {{ $totalAnak ?? 0 }};
    const ctx2 = document.getElementById('chartDonut').getContext('2d');

    // Jika semua 0, tampilkan donut abu-abu sebagai empty state
    const donutData   = (todayHadir === 0 && todayAlpa === 0) ? [1] : [todayHadir, todayAlpa];
    const donutColors = (todayHadir === 0 && todayAlpa === 0) ? ['#dee2e6'] : ['#28a745', '#dc3545'];
    const donutLabels = (todayHadir === 0 && todayAlpa === 0) ? ['Tidak Ada Data'] : ['Hadir', 'Tidak Hadir'];

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: donutLabels,
            datasets: [{ data: donutData, backgroundColor: donutColors, borderWidth: 0 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: (todayHadir > 0 || todayAlpa > 0) }
            },
            cutout: '70%'
        }
    });

    // Tampilkan persentase kehadiran di tengah donut jika ada data
    if (todayHadir > 0 || todayAlpa > 0) {
        Chart.register({
            id: 'donutCenterText',
            afterDraw(chart) {
                if (chart.canvas.id !== 'chartDonut') return;
                const { width, height, ctx } = chart;
                ctx.save();
                const total = todayHadir + todayAlpa;
                const pct   = total > 0 ? Math.round((todayHadir / total) * 100) : 0;
                ctx.font = 'bold 22px Inter, sans-serif';
                ctx.fillStyle = '#28a745';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(pct + '%', width / 2, height / 2 - 8);
                ctx.font = '12px Inter, sans-serif';
                ctx.fillStyle = '#6c757d';
                ctx.fillText('Hadir', width / 2, height / 2 + 14);
                ctx.restore();
            }
        });
    }
</script>
@endpush