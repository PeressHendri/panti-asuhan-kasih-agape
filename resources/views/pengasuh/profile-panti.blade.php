@extends('layouts.app')

@section('title', 'Profil Data Anak')
@section('header-title', 'Data Anak Panti')

@push('styles')
<style>
    /* Style untuk card view di mobile */
    @media (max-width: 767px) {
        .table-responsive {
            display: none; /* Sembunyikan tabel di mobile */
        }
        .child-card-view {
            display: block !important; /* Tampilkan card view di mobile */
        }
    }
    .child-card {
        background-color: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .child-card-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 50%;
    }
    .child-card-actions {
        margin-left: auto;
    }

    /* Efek hover pada tabel di desktop */
    .table tbody tr {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .table tbody tr:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 10;
        position: relative;
    }
</style>
@endpush

@section('content')

<div class="card">
    <div class="card-header">
        <div class="row gy-3 align-items-center">
            <div class="col-md-3"><h5 class="mb-0">Daftar Anak</h5></div>
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
                        <th style="width: 80px;">Foto</th>
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
                                    <img src="{{ asset('storage/' . $child->photo) }}" alt="{{ $child->nama }}" class="child-card-img">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 60px; height: 60px;"><i class="fas fa-user fa-lg text-secondary"></i></div>
                                @endif
                            </td>
                            <td>{{ $child->nama }}</td>
                            <td class="fw-bold">{{ $child->nim ?? '-' }}</td>
                            <td>{{ $child->tanggal_lahir ? $child->tanggal_lahir->age . ' tahun' : '-' }}</td>
                            <td>{{ $child->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" title="Lihat Detail" data-bs-toggle="modal" data-bs-target="#detailChildModal" data-child='@json($child)'>
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route(auth()->user()->role . '.profile.panti.edit', $child->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-name="{{ $child->nama }}" data-url="{{ route(auth()->user()->role . '.profile.panti.destroy', $child->id) }}"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="child-card-view d-none">
            @forelse($children as $child)
                <div class="child-card">
                    <img src="{{ $child->photo ? asset('storage/' . $child->photo) : asset('images/default-avatar.png') }}" alt="{{ $child->nama }}" class="child-card-img">
                    <div class="child-card-info">
                        <h6 class="mb-0">{{ $child->nama }}</h6>
                        <small class="text-muted">{{ $child->tanggal_lahir ? $child->tanggal_lahir->age . ' tahun' : 'Umur tidak diketahui' }}</small>
                    </div>
                    <div class="child-card-actions">
                        <button type="button" class="btn btn-sm btn-info" title="Lihat Detail" data-bs-toggle="modal" data-bs-target="#detailChildModal" data-child='@json($child)'>
                            <i class="fas fa-eye"></i>
                        </button>
                         <a href="{{ route(auth()->user()->role . '.profile.panti.edit', $child->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted">Data tidak ditemukan.</div>
            @endforelse
        </div>

    </div>
    @if ($children->hasPages())
    <div class="card-footer">
        {{ $children->appends(['search' => request('search')])->links() }}
    </div>
    @endif
</div>

<div class="modal fade" id="detailChildModal" tabindex="-1" aria-labelledby="detailChildModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailChildModalLabel">Detail Anak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="detail_photo" src="" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" alt="Foto Anak">
                    <h4 id="detail_nama" class="mt-2 mb-0"></h4>
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">NIK</th>
                        <td id="detail_nim"></td>
                    </tr>
                     <tr>
                        <th>Tanggal Lahir</th>
                        <td id="detail_tanggal_lahir"></td>
                    </tr>
                    <tr>
                        <th>Usia</th>
                        <td id="detail_usia"></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td id="detail_jenis_kelamin"></td>
                    </tr>
                    <tr>
                        <th>Sekolah</th>
                        <td id="detail_sekolah"></td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td id="detail_catatan"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center">
                <p class="fs-5">Yakin ingin menghapus data <br><strong id="childNameToDelete"></strong>?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Skrip untuk Modal Hapus
    const deleteModal = document.getElementById('deleteConfirmModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const name = button.getAttribute('data-name');
        const url = button.getAttribute('data-url');
        deleteModal.querySelector('#childNameToDelete').textContent = name;
        deleteModal.querySelector('#deleteForm').setAttribute('action', url);
    });

    // Skrip untuk Modal Detail
    const detailModal = document.getElementById('detailChildModal');
    detailModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const childData = JSON.parse(button.getAttribute('data-child'));
        
        // Fungsi untuk format tanggal
        const formatDate = (dateString) => {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        };
        
        // Fungsi untuk menghitung umur
        const calculateAge = (dateString) => {
            if (!dateString) return '-';
            const birthDate = new Date(dateString);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age + ' tahun';
        };

        // Mengisi modal dengan data
        const defaultAvatar = "{{ asset('images/default-avatar.png') }}";
        const photoUrl = childData.photo ? `{{ asset('storage') }}/${childData.photo}` : defaultAvatar;

        detailModal.querySelector('#detail_photo').src = photoUrl;
        detailModal.querySelector('#detail_nama').textContent = childData.nama || '-';
        detailModal.querySelector('#detail_nim').textContent = childData.nim || '-';
        detailModal.querySelector('#detail_tanggal_lahir').textContent = formatDate(childData.tanggal_lahir);
        detailModal.querySelector('#detail_usia').textContent = calculateAge(childData.tanggal_lahir);
        detailModal.querySelector('#detail_jenis_kelamin').textContent = childData.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
        detailModal.querySelector('#detail_sekolah').textContent = childData.sekolah || '-';
        detailModal.querySelector('#detail_catatan').textContent = childData.catatan || '-';
    });
});
</script>
@endpush    