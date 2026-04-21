@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('header-title', 'Dashboard Admin')

@section('content')
<style>
    .stat-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        height: 100%;
        padding: 20px;
    }

    .stat-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        font-size: 1.5rem;
        color: white;
    }

    .bg-indigo { background-color: #6610f2; }
    .bg-emerald { background-color: #10b981; }
    .bg-pink { background-color: #f472b6; }
    .bg-sky { background-color: #38bdf8; }
    .bg-rose { background-color: #fb7185; }

    .chart-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        background: white;
        padding: 20px;
        margin-bottom: 20px;
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 20px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    }
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="stats-grid">
    <div class="card stat-card text-center">
        <div class="stat-icon-wrapper bg-indigo">
            <i class="fas fa-child"></i>
        </div>
        <h3 class="fw-bold mb-0">{{ $stats['total_children'] ?? 0 }}</h3>
        <p class="text-muted small mb-0">Total Anak Panti</p>
    </div>
    <div class="card stat-card text-center">
        <div class="stat-icon-wrapper bg-emerald">
            <i class="fas fa-users"></i>
        </div>
        <h3 class="fw-bold mb-0">{{ $stats['total_users'] ?? 0 }}</h3>
        <p class="text-muted small mb-0">Total Pengguna</p>
    </div>
    <div class="card stat-card text-center">
        <div class="stat-icon-wrapper bg-pink">
            <i class="fas fa-user-shield"></i>
        </div>
        <h3 class="fw-bold mb-0">{{ $stats['total_admin'] ?? 0 }}</h3>
        <p class="text-muted small mb-0">Admin</p>
    </div>
    <div class="card stat-card text-center">
        <div class="stat-icon-wrapper bg-sky">
            <i class="fas fa-user-tie"></i>
        </div>
        <h3 class="fw-bold mb-0">{{ $stats['total_pengasuh'] ?? 0 }}</h3>
        <p class="text-muted small mb-0">Pengasuh</p>
    </div>
    <div class="card stat-card text-center">
        <div class="stat-icon-wrapper bg-rose">
            <i class="fas fa-hand-holding-heart"></i>
        </div>
        <h3 class="fw-bold mb-0">{{ $stats['total_sponsor'] ?? 0 }}</h3>
        <p class="text-muted small mb-0">Sponsor</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="chart-card">
            <h6 class="section-title"><i class="fas fa-chart-line text-primary"></i> Tren Kehadiran 7 Hari Terakhir</h6>
            <div style="height: 350px;">
                <canvas id="chartKehadiran"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <h6 class="section-title">Aktivitas Terbaru</h6>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
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
                            <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>{{ $log->activity }}</td>
                            <td><span class="badge bg-success">Berhasil</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada aktivitas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="chart-card text-center">
            <h6 class="section-title text-start"><i class="fas fa-chart-pie text-success"></i> Kehadiran Hari Ini</h6>
            <div style="position: relative; width: 250px; margin: 0 auto;">
                <canvas id="chartDonut"></canvas>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    @php 
                        $total = ($todayHadir ?? 0) + ($todayAlpa ?? 0);
                        $hasData = $total > 0;
                    @endphp
                    @if(!$hasData)
                        <i class="fas fa-moon fa-3x text-light mb-2"></i>
                        <p class="small text-muted mb-0">Belum Ada<br>Data Hari Ini</p>
                    @else
                        <h4 class="fw-bold mb-0">{{ round(($todayHadir / $total) * 100) }}%</h4>
                        <p class="small text-muted mb-0">Hadir</p>
                    @endif
                </div>
            </div>
            <div class="mt-4">
                <div class="d-flex justify-content-center gap-3">
                    <div>
                        <span class="badge bg-success px-3">Hadir: {{ $todayHadir ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="badge bg-danger px-3">Alpa: {{ $todayAlpa ?? 0 }}</span>
                    </div>
                </div>
                <p class="mt-3 text-muted small">Total Anak: {{ $stats['total_children'] ?? 22 }}</p>
            </div>
        </div>

        <div class="chart-card">
            <h6 class="section-title">Informasi Sistem</h6>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>Status Server</span>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>Database</span>
                    <span class="badge bg-success">Terhubung</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>CCTV Engine</span>
                    @if($isPiOnline)
                        <span class="badge bg-success">Online</span>
                    @else
                        <span class="badge bg-danger">Offline</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Bar Chart
    const ctx1 = document.getElementById('chartKehadiran').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                { 
                    label: 'Hadir', 
                    data: {!! json_encode($chartHadir) !!}, 
                    backgroundColor: '#10b981'
                },
                { 
                    label: 'Alpa', 
                    data: {!! json_encode($chartAlpa) !!}, 
                    backgroundColor: '#fb7185'
                },
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } 
        }
    });

    // Donut Chart
    const ctx2 = document.getElementById('chartDonut').getContext('2d');
    const todayHadir = {{ $todayHadir ?? 0 }};
    const todayAlpa  = {{ $todayAlpa ?? 0 }};
    
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            datasets: [{ 
                data: (todayHadir === 0 && todayAlpa === 0) ? [1] : [todayHadir, todayAlpa], 
                backgroundColor: (todayHadir === 0 && todayAlpa === 0) ? ['#e5e7eb'] : ['#10b981', '#ef4444'], 
                borderWidth: 0 
            }]
        },
        options: { 
            responsive: true, 
            cutout: '80%',
            plugins: { tooltip: { enabled: (todayHadir > 0 || todayAlpa > 0) } } 
        }
    });
</script>
@endpush

<style>
    /* Fixed 5 column layout for cards */
    @media (min-width: 992px) {
        .col-md-2-4 {
            width: 20%;
            flex: 0 0 20%;
        }
    }
</style>