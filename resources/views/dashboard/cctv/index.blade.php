@extends('layouts.app')

@section('title', 'Monitoring CCTV')
@section('header-title', 'Monitoring CCTV')

@push('styles')
    <style>
        .cctv-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #334155;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .cctv-header {
            background: #0f172a;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #334155;
        }

        .cctv-title {
            color: #f8fafc;
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .cctv-body {
            flex: 1;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .cctv-footer {
            padding: 10px 15px;
            background: #1e293b;
            font-size: 0.85rem;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        .pulse-alert {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        table.log-table th,
        table.log-table td {
            vertical-align: middle;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
@endpush

@section('content')
    @php
        $maxPing = \App\Models\CctvCamera::max('last_ping');
        $isPiOnline = $maxPing && \Carbon\Carbon::parse($maxPing)->diffInMinutes(now()) <= 1;
    @endphp

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-primary fs-6 py-2 px-3 shadow-sm">
                    <i class="fas fa-video text-white me-2"></i> Sistem Pemantauan Kamera
                </span>
            </div>
            <div>
                @if(auth()->user()->role === 'admin')
                    <button type="button" class="btn btn-primary btn-sm me-2 shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalKamera">
                        <i class="fas fa-cog me-1"></i> Atur Kamera
                    </button>
                @endif
                <span class="text-muted small">Update terakhir: {{ now()->format('H:i:s') }}</span>
            </div>
        </div>

        <!-- Privacy Notice -->
        <div class="alert alert-info shadow-sm bg-white border-0 border-start border-4 border-info rounded-3 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                <div>
                    <strong>Catatan Privasi:</strong>
                    <p class="mb-0 text-muted">Pemantauan CCTV hanya mencakup area umum (Ruang Belajar, Ruang Ibadah, Ruang
                        Bersama, dan Halaman). Kamar tidur dan kamar mandi tidak dipantau demi privasi anak.</p>
                </div>
            </div>
        </div>

        <!-- Camera Grid -->
        <div class="row row-cols-1 row-cols-lg-2 g-4 mb-5">
            @foreach($cameras as $camera)
                <div class="col">
                    <div class="cctv-card shadow-sm">
                        <div class="cctv-header">
                            <h5 class="cctv-title"><i class="fas fa-video me-2 text-primary"></i>{{ $camera->nama }}</h5>
                            <div class="d-flex align-items-center">
                                @if(auth()->user()->role === 'admin')
                                    <div class="dropdown me-3">
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 dropdown-toggle border-0"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:#94a3b8;">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#modalEditCam{{ $camera->id }}"><i
                                                        class="fas fa-edit me-2 text-primary"></i>Edit Kamera</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#modalDeleteCam{{ $camera->id }}"><i
                                                        class="fas fa-trash-alt me-2"></i>Hapus Kamera</a></li>
                                        </ul>
                                    </div>
                                @endif

                                @if($camera->is_online)
                                    <span class="badge bg-success">ONLINE</span>
                                @else
                                    <span class="badge bg-secondary">OFFLINE</span>
                                @endif
                            </div>
                        </div>

                        <div class="cctv-body">
                            @if($camera->is_online && !empty($camera->hls_url))
                                <video id="cam-{{ $camera->kamera_id }}" controls autoplay muted
                                    style="width:100%; height:100%; object-fit:cover;"></video>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        var video = document.getElementById('cam-{{ $camera->kamera_id }}');
                                        var hlsSrc = '{{ $camera->hls_url }}';
                                        if (Hls.isSupported()) {
                                            var hls = new Hls();
                                            hls.loadSource(hlsSrc);
                                            hls.attachMedia(video);
                                            hls.on(Hls.Events.MANIFEST_PARSED, function () {
                                                video.play();
                                            });
                                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                            video.src = hlsSrc;
                                            video.addEventListener('loadedmetadata', function () {
                                                video.play();
                                            });
                                        }
                                    });
                                </script>
                            @else
                                <div style="text-align:center; color:#64748b; padding: 40px;">
                                    <i class="fas fa-video-slash fa-3x mb-3"></i>
                                    <h5>Kamera Offline</h5>
                                    <small>Menunggu koneksi streaming...</small>
                                </div>
                            @endif
                        </div>

                        <div class="cctv-footer">
                            <div>
                                <i class="fas fa-clock me-1"></i> Ping:
                                {{ $camera->last_ping ? \Carbon\Carbon::parse($camera->last_ping)->diffForHumans() : 'Belum pernah' }}
                            </div>
                            <div>
                                @php
                                    // Check motion in last 5 mins
                                    $recentMotion = \App\Models\CctvActivityLog::where('kamera_id', $camera->kamera_id)
                                        ->where('jenis_aktivitas', 'motion')
                                        ->where('waktu', '>=', now()->subMinutes(5))
                                        ->exists();
                                @endphp
                                @if($recentMotion)
                                    <span class="badge bg-warning text-dark pulse-alert"><i class="fas fa-walking me-1"></i> Motion
                                        Terdeteksi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($cameras->isEmpty())
                <div class="col-12">
                    <div class="text-center p-5 bg-white rounded-3 shadow-sm border border-light">
                        <i class="fas fa-video-slash fa-4x text-muted mb-3"></i>
                        <h4>Belum Ada Kamera</h4>
                        <p class="text-muted">Tidak ada kamera yang dikonfigurasi aktif saat ini.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Logs Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="color: var(--text-color);"><i
                        class="fas fa-list-ul me-2 text-primary"></i>Log Aktivitas CCTV</h5>
                <span class="badge bg-light text-dark border"><i class="fas fa-sync-alt fa-spin me-1"></i> Auto-refresh
                    30s</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table log-table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Waktu</th>
                                <th>Kamera</th>
                                <th>Jenis Aktivitas</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activityLogs as $log)
                                <tr>
                                    <td class="ps-4 text-nowrap"><i
                                            class="far fa-clock text-muted me-2"></i>{{ $log->waktu->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td>{{ $log->camera ? $log->camera->nama : $log->kamera_id }}</td>
                                    <td>
                                        @if($log->jenis_aktivitas == 'motion')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-walking me-1"></i>
                                                Motion</span>
                                        @elseif($log->jenis_aktivitas == 'online')
                                            <span class="badge bg-success"><i class="fas fa-wifi me-1"></i> Online</span>
                                        @elseif($log->jenis_aktivitas == 'offline')
                                            <span class="badge bg-danger"><i class="fas fa-unlink me-1"></i> Offline</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->jenis_aktivitas }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $log->keterangan }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center p-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                                        <p class="mb-0">Belum ada log aktivitas tercatat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($activityLogs->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $activityLogs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Tambah Kamera -->
    @if(auth()->check() && auth()->user()->role === 'admin')
        <div class="modal fade" id="modalKamera" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('admin.cctv.store') }}" method="POST">
                    @csrf
                    <div class="modal-content border-0">
                        <div class="modal-header bg-primary text-white border-0">
                            <h5 class="modal-title"><i class="fas fa-video me-2"></i>Tambah Kamera CCTV</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID Kamera</label>
                                <input type="text" name="kamera_id" class="form-control" placeholder="Contoh: cam_halaman_depan"
                                    required>
                                <small class="text-muted">ID unik untuk endpoint API. Tidak boleh ada spasi.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Tampilan</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: Kamera Halaman Depan"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">URL HLS (Stream Browser)</label>
                                <input type="url" name="hls_url" class="form-control"
                                    placeholder="https://domain.com/stream.m3u8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">URL RTSP Mentah</label>
                                <input type="text" name="rtsp_url" class="form-control"
                                    placeholder="rtsp://admin:pass@ip:port/stream">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Lokasi Area</label>
                                <input type="text" name="lokasi" class="form-control" placeholder="Halaman Depan Panti">
                            </div>
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" checked>
                                <label class="form-check-label fw-bold" for="isActiveCheck">Kamera Aktif Ditampilkan?</label>
                            </div>
                        </div>
                        <div class="modal-footer border-0 card-footer bg-light px-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-4">Simpan Kamera</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modals for Edit & Delete per Camera -->
        @foreach($cameras as $camera)
            <!-- Modal Edit -->
            <div class="modal fade" id="modalEditCam{{ $camera->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('admin.cctv.update', $camera->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content border-0">
                            <div class="modal-header bg-dark text-white border-0">
                                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Kamera CCTV</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">ID Kamera (Tetap)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $camera->kamera_id }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Tampilan</label>
                                    <input type="text" name="nama" class="form-control" value="{{ $camera->nama }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">URL HLS (Stream Browser)</label>
                                    <input type="url" name="hls_url" class="form-control" value="{{ $camera->hls_url }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">URL RTSP Mentah</label>
                                    <input type="text" name="rtsp_url" class="form-control" value="{{ $camera->rtsp_url }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Lokasi Area</label>
                                    <input type="text" name="lokasi" class="form-control" value="{{ $camera->lokasi }}">
                                </div>
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                        id="isActiveCheck{{ $camera->id }}" {{ $camera->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="isActiveCheck{{ $camera->id }}">Kamera Aktif
                                        Ditampilkan?</label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 card-footer bg-light px-4">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Delete -->
            <div class="modal fade" id="modalDeleteCam{{ $camera->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('admin.cctv.destroy', $camera->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-content border-0">
                            <div class="modal-header border-0 pb-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 text-center">
                                <i class="fas fa-exclamation-circle text-danger fa-4x mb-3"></i>
                                <h4 class="fw-bold">Hapus Kamera CCTV?</h4>
                                <p class="text-muted mb-0">Apakah Anda yakin ingin menghapus sistem kamera
                                    <strong>{{ $camera->nama }}</strong>? Data aktivitas kamera ini mungkin akan kehilangan
                                    referensi.</p>
                            </div>
                            <div class="modal-footer border-0 justify-content-center pb-4">
                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger px-4">Ya, Hapus</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

@endsection

@push('scripts')
    <script>
        // Auto refresh halaman setiap 30 detik
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
@endpush