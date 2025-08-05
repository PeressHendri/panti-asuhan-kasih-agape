@extends('layouts.app')

@section('title', 'Profil Data Anak')
@section('header-title', 'Data Anak Panti')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="row gy-3 align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0">Daftar Anak</h5>
            </div>
            <div class="col-md-5">
                <form action="{{ route(auth()->user()->role . '.profile.panti') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIK..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-md-end">
                @if (auth()->user()->role === 'admin' || auth()->user()->role === 'pengasuh')
                    <a href="{{ route(auth()->user()->role . '.profile.panti.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Data Anak
                    </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Usia</th>
                        <th>Jenis Kelamin</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($children as $child)
                        <tr>
                            <td>
                                @if($child->photo)
                                    <img src="{{ asset('storage/' . $child->photo) }}" alt="{{ $child->nama }}" style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 60px; height: 60px;"><i class="fas fa-user fa-lg text-secondary"></i></div>
                                @endif
                            </td>
                            <td>{{ $child->nama }}</td>
                            <td class="fw-bold">{{ $child->nim ?? '-' }}</td>
                            <td>{{ $child->tanggal_lahir ? $child->tanggal_lahir->age . ' tahun' : '-' }}</td>
                            <td>{{ $child->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="text-center">
                                <a href="{{ route(auth()->user()->role . '.profile.panti.edit', $child->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                
                                {{-- Tombol ini sudah benar, tidak perlu diubah --}}
                                <button type="button" class="btn btn-sm btn-danger" title="Hapus" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteConfirmModal"
                                        data-name="{{ $child->nama }}"
                                        data-url="{{ route(auth()->user()->role . '.profile.panti.destroy', $child->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($children->hasPages())
    <div class="card-footer">
        {{ $children->appends(['search' => request('search')])->links() }}
    </div>
    @endif
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="fs-5">Yakin ingin menghapus data anak <br><strong id="childNameToDelete"></strong>?</p>
                <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST"> {{-- action URL akan diisi oleh JavaScript --}}
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- BAGIAN YANG DIPERBAIKI --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    
    // Pastikan modal ada sebelum menambahkan event listener
    if (deleteConfirmModal) {
        // Event ini akan berjalan TEPAT SEBELUM modal ditampilkan
        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
            // Dapatkan tombol yang memicu modal
            const button = event.relatedTarget;

            // Ekstrak informasi dari atribut data-*
            const childName = button.getAttribute('data-name');
            const formActionUrl = button.getAttribute('data-url');

            // Dapatkan elemen di dalam modal untuk diperbarui
            const modalBodyName = deleteConfirmModal.querySelector('#childNameToDelete');
            const deleteForm = deleteConfirmModal.querySelector('#deleteForm');

            // Perbarui konten modal dengan data yang benar
            modalBodyName.textContent = childName;
            deleteForm.setAttribute('action', formActionUrl);
        });
    }
});
</script>
@endpush