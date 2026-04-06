@extends('layouts.app')
@section('title', 'Manajemen Donasi')
@section('header-title', 'Verifikasi Laporan Donasi')

@push('styles')
<style>
    .stat-pill { border-radius: 12px; padding: 16px 20px; font-weight: 700; text-align: center; }
    .donation-card { border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; transition: box-shadow 0.2s; }
    .donation-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .badge-pending    { background: #fff3cd; color: #856404; }
    .badge-konfirmasi { background: #d1e7dd; color: #0f5132; }
    .badge-ditolak    { background: #f8d7da; color: #842029; }
    .proof-img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-warning bg-opacity-10 text-warning border border-warning">
                <div style="font-size:1.6rem;">{{ $stats['pending'] }}</div>
                <div class="small fw-semibold">Menunggu Verifikasi</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-success bg-opacity-10 text-success border border-success">
                <div style="font-size:1.6rem;">{{ $stats['konfirmasi'] }}</div>
                <div class="small fw-semibold">Dikonfirmasi</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-danger bg-opacity-10 text-danger border border-danger">
                <div style="font-size:1.6rem;">{{ $stats['ditolak'] }}</div>
                <div class="small fw-semibold">Ditolak</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-primary bg-opacity-10 text-primary border border-primary">
                <div style="font-size:1.6rem;">{{ $stats['total'] }}</div>
                <div class="small fw-semibold">Total Donasi</div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="d-flex gap-2 mb-4 flex-wrap">
        @foreach(['all' => 'Semua', 'pending' => 'Pending', 'konfirmasi' => 'Konfirmasi', 'ditolak' => 'Ditolak'] as $key => $label)
            <a href="{{ route('admin.donasi', ['status' => $key]) }}"
               class="btn btn-sm rounded-pill {{ $status === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                @if($key === 'pending' && $stats['pending'] > 0)
                    <span class="badge bg-danger ms-1">{{ $stats['pending'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-uppercase small">
                    <tr>
                        <th class="ps-4">Donatur</th>
                        <th>Jenis</th>
                        <th>Jumlah / Keterangan</th>
                        <th>Tanggal</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($donations as $d)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $d->nama_donatur }}</div>
                            <div class="text-muted small">{{ $d->email }}</div>
                            @if($d->child)
                                <div class="text-info small"><i class="fas fa-child me-1"></i>{{ $d->child->nama }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge rounded-pill
                                @if($d->jenis_donasi === 'uang') bg-success
                                @elseif($d->jenis_donasi === 'barang') bg-info text-dark
                                @else bg-primary
                                @endif">
                                {{ strtoupper(str_replace('_', ' ', $d->jenis_donasi)) }}
                            </span>
                        </td>
                        <td>
                            @if($d->jenis_donasi === 'uang')
                                <strong class="text-success">Rp {{ number_format($d->jumlah, 0, ',', '.') }}</strong>
                                @if($d->nomor_resi)
                                    <div class="text-muted small">Resi: {{ $d->nomor_resi }}</div>
                                @endif
                            @else
                                <span class="small text-muted">{{ Str::limit($d->keterangan, 60) }}</span>
                            @endif
                        </td>
                        <td class="small">{{ \Carbon\Carbon::parse($d->tanggal)->format('d M Y') }}</td>
                        <td>
                            @if($d->bukti_transfer_path)
                                <a href="{{ asset('storage/' . $d->bukti_transfer_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $d->bukti_transfer_path) }}" class="proof-img" alt="Bukti">
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge px-3 py-2 rounded-pill badge-{{ $d->status }}">
                                {{ strtoupper($d->status) }}
                            </span>
                            @if($d->catatan_admin)
                                <div class="text-muted small mt-1" style="max-width:140px;">{{ $d->catatan_admin }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($d->status === 'pending')
                                <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                    data-bs-target="#modalVerify{{ $d->id }}">
                                    <i class="fas fa-check-circle me-1"></i>Verifikasi
                                </button>
                            @else
                                <span class="text-muted small">Selesai</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Verifikasi --}}
                    <div class="modal fade" id="modalVerify{{ $d->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('admin.donasi.verify', $d->id) }}" method="POST">
                                @csrf
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-shield-check text-primary me-2"></i>Verifikasi Donasi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-3">
                                            Donasi dari <strong>{{ $d->nama_donatur }}</strong>
                                            @if($d->jenis_donasi === 'uang') senilai <strong class="text-success">Rp {{ number_format($d->jumlah,0,',','.') }}</strong> @endif
                                        </p>
                                        @if($d->bukti_transfer_path)
                                            <div class="text-center mb-3">
                                                <img src="{{ asset('storage/' . $d->bukti_transfer_path) }}" class="img-fluid rounded-3 shadow-sm" style="max-height:200px;">
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Keputusan</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="konfirmasi{{ $d->id }}" value="konfirmasi" required checked>
                                                    <label class="form-check-label text-success fw-bold" for="konfirmasi{{ $d->id }}">✅ Konfirmasi</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="ditolak{{ $d->id }}" value="ditolak">
                                                    <label class="form-check-label text-danger fw-bold" for="ditolak{{ $d->id }}">❌ Tolak</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Catatan Admin (opsional)</label>
                                            <textarea name="catatan_admin" class="form-control" rows="2" placeholder="Misal: Transfer sudah diterima..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-1"></i>Simpan Keputusan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data donasi masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3 d-flex justify-content-center">
        {{ $donations->appends(request()->query())->links() }}
    </div>

</div>
@endsection
