@extends('layouts.app')

@section('title', 'Face Recognition Log')
@section('header-title', 'Face Recognition Log')

@push('styles')
<style>
    .pulse {
        animation: pulse-red 1.5s infinite;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .stats-card {
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        color: white;
    }
    .stats-icon {
        background: rgba(255, 255, 255, 0.2);
        padding: 15px;
        border-radius: 50%;
        margin-right: 15px;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 60px;
        height: 60px;
    }
    .stats-icon i {
        font-size: 1.5rem;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    
    .thumbnail-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        transition: transform 0.2s;
    }
    .thumbnail-img:hover {
        transform: scale(1.1);
        border-color: #3b82f6;
    }
</style>
@endpush

@section('content')

<div class="container-fluid py-4">
    @php
        $maxPing = \App\Models\CctvCamera::max('last_ping');
        $isPiOnline = $maxPing && \Carbon\Carbon::parse($maxPing)->diffInMinutes(now()) <= 1;
    @endphp

    <!-- Header Status -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            @if($isPiOnline)
                <span class="badge bg-success fs-6 py-2 px-3 shadow-sm">
                    <i class="fas fa-circle text-white me-2 pulse" style="animation-name: none;"></i> 🟢 Raspberry Pi Online
                </span>
            @else
                <span class="badge bg-danger fs-6 py-2 px-3 shadow-sm">
                    <i class="fas fa-plug text-white me-2"></i> 🔴 Raspberry Pi Offline
                </span>
            @endif
        </div>
        <div>
            <span class="text-muted small">Update terakhir: {{ now()->format('H:i:s') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
        <div class="col">
            <div class="stats-card bg-gradient-primary">
                <div class="stats-icon"><i class="fas fa-eye"></i></div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                    <div class="text-white-50">Total Deteksi</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stats-card bg-gradient-success">
                <div class="stats-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ $stats['check_in'] }}</h3>
                    <div class="text-white-50">Check-In Berhasil</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stats-card bg-gradient-warning">
                <div class="stats-icon"><i class="fas fa-sign-out-alt"></i></div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ $stats['check_out'] }}</h3>
                    <div class="text-white-50">Check-Out</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stats-card bg-gradient-danger {{ $stats['tidak_dikenal'] > 0 ? 'pulse' : '' }}">
                <div class="stats-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <h3 class="mb-0 fw-bold">{{ $stats['tidak_dikenal'] }}</h3>
                    <div class="text-white-50">Tidak Dikenal</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Panel -->
    @if($stats['tidak_dikenal'] > 0)
    <div class="alert alert-danger shadow-sm border border-danger border-2 rounded-3 mb-4 d-flex align-items-center">
        <i class="fas fa-biohazard fa-3x me-4 text-danger pulse"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Peringatan Keamanan!</h5>
            <p class="mb-0">Terdeteksi <strong>{{ $stats['tidak_dikenal'] }}</strong> wajah tidak dikenal hari ini. Harap periksa log aktivitas di bawah.</p>
        </div>
    </div>
    @endif

    <!-- Filter Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-white rounded-3 p-3">
            <form action="{{ route('dashboard.face-log') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="col-form-label"><i class="far fa-calendar-alt me-2 text-muted"></i>Tanggal:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
                </div>
                <div class="col-auto ms-lg-3">
                    <label class="col-form-label"><i class="fas fa-filter me-2 text-muted"></i>Status:</label>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="check_in" {{ request('status') == 'check_in' ? 'selected' : '' }}>Check-In</option>
                        <option value="check_out" {{ request('status') == 'check_out' ? 'selected' : '' }}>Check-Out</option>
                        <option value="tidak_dikenal" {{ request('status') == 'tidak_dikenal' ? 'selected' : '' }}>Tidak Dikenal</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-search me-2"></i>Terapkan</button>
                    <a href="{{ route('dashboard.face-log') }}" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold" style="color: var(--text-color);"><i class="fas fa-clipboard-list me-2 text-primary"></i>Daftar Log Deteksi</h5>
            <span class="badge bg-light text-dark border"><i class="fas fa-sync-alt fa-spin me-1"></i> Auto-refresh 30s</span>
        </div>
        <div class="card-body p-0">
            @if($logs->isEmpty() && !request()->filled('date') && !request()->filled('status'))
                <!-- Empty State (No Data from Pi Yet) -->
                <div style="text-align:center; padding:60px; color:#64748b;">
                    <i class="fas fa-fingerprint fa-4x mb-4" style="color:#FF6B1A;"></i>
                    <h4>Menunggu Data dari Raspberry Pi</h4>
                    <p class="mb-2">Sistem siap menerima data face recognition.</p>
                    <p>Pastikan Raspberry Pi sudah terhubung dan script berjalan di panti.</p>
                    <div class="bg-light d-inline-block px-4 py-2 mt-3 rounded-2 shadow-sm border">
                        <small class="text-muted d-block mb-1">API Webhook Token:</small>
                        <code class="fs-6 user-select-all">{{ config('app.raspberry_pi_token', 'kasihagape2025secret') }}</code>
                    </div>
                </div>
            @elseif($logs->isEmpty())
                <div style="text-align:center; padding:60px; color:#64748b;">
                    <i class="fas fa-search fa-4x mb-4 text-light"></i>
                    <h4>Tidak Ada Data</h4>
                    <p class="mb-0">Tidak ditemukan riwayat log deteksi dengan filter yang dipilih.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Capture</th>
                                <th>Nama Identitas</th>
                                <th>Status</th>
                                <th>Waktu Deteksi</th>
                                <th>Confidence</th>
                                <th>Algoritma</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        @if($log->foto_capture_path)
                                            <img src="{{ asset('storage/' . $log->foto_capture_path) }}" 
                                                 alt="Capture Face" 
                                                 class="thumbnail-img" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#photoModal{{ $log->id }}">
                                                 
                                            <!-- Modal Photo -->
                                            <div class="modal fade" id="photoModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 overflow-hidden">
                                                        <div class="modal-header border-0 bg-dark text-white">
                                                            <h5 class="modal-title">Hasil Tangkapan Wajah</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-0 text-center bg-dark">
                                                            <img src="{{ asset('storage/' . $log->foto_capture_path) }}" class="img-fluid" alt="Face Capture Full">
                                                        </div>
                                                        <div class="modal-footer border-0 card-footer bg-light px-4 pb-4">
                                                            <div class="w-100 d-flex justify-content-between text-muted fs-6">
                                                                <span><i class="fas fa-clock me-1"></i>{{ $log->waktu_deteksi->format('H:i') }}</span>
                                                                <span><i class="fas fa-camera me-1"></i>Cam {{ $log->kamera_id }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="thumbnail-img bg-light d-flex align-items-center justify-content-center border">
                                                <i class="fas fa-user-slash text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status == 'tidak_dikenal')
                                            <span class="text-danger fw-bold"><i class="fas fa-question-circle me-1"></i> Tidak Dikenal</span>
                                        @else
                                            <span class="fw-bold">{{ $log->nama }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status == 'check_in')
                                            <span class="badge bg-success py-2 px-3"><i class="fas fa-sign-in-alt me-1"></i> Check-In</span>
                                        @elseif($log->status == 'check_out')
                                            <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-sign-out-alt me-1"></i> Check-Out</span>
                                        @elseif($log->status == 'tidak_dikenal')
                                            <span class="badge bg-danger py-2 px-3"><i class="fas fa-times me-1"></i> Tidak Dikenal</span>
                                        @else
                                            <span class="badge bg-secondary py-2 px-3">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="far fa-calendar-alt text-muted me-2"></i>{{ $log->waktu_deteksi->format('d/m/Y') }}</div>
                                        <div><i class="far fa-clock text-muted me-2"></i><span class="fw-bold fs-6">{{ $log->waktu_deteksi->format('H:i:s') }}</span></div>
                                    </td>
                                    <td>
                                        @php
                                            $conf = $log->confidence_score ?? 0;
                                            $colorClass = $conf >= 80 ? 'bg-success' : ($conf >= 50 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 fw-semibold">{{ number_format($conf, 1) }}%</span>
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $conf }}%;" aria-valuenow="{{ $conf }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary text-uppercase">{{ $log->algoritma ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        @if($logs->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Hanya auto-refresh jika url tidak memiliki parameter date atau status / khusus hari ini.
    const urlParams = new URLSearchParams(window.location.search);
    const dateQuery = urlParams.get('date');
    const today = new Date().toISOString().split('T')[0];

    if (!dateQuery || dateQuery === today) {
        setTimeout(() => {
            location.reload();
        }, 30000);
    }
</script>
@endpush
