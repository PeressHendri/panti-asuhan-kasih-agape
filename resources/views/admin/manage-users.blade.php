@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('header-title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pengguna</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="fas fa-plus me-2"></i>Tambah Pengguna
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Role</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                @if ($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}" width="50" height="50" style="object-fit:cover;" class="rounded-circle" alt="Foto">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-secondary"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle">{{ $user->name }}</td>
                            <td class="align-middle">{{ $user->email }}</td>
                            <td class="align-middle">{{ $user->phone }}</td>
                            <td class="align-middle">
                                @php
                                    $roleClass = '';
                                    switch ($user->role) {
                                        case 'admin':
                                            $roleClass = 'bg-danger';
                                            break;
                                        case 'pengasuh':
                                            $roleClass = 'bg-primary';
                                            break;
                                        case 'donatur':
                                            $roleClass = 'bg-success';
                                            break;
                                        default:
                                            $roleClass = 'bg-secondary';
                                    }
                                @endphp
                                <span class="badge {{ $roleClass }} text-uppercase">{{ $user->role }}</span>
                            </td>
                            <td class="text-center align-middle">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $user->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($users->hasPages())
    <div class="card-footer">
        {{ $users->links() }}
    </div>
    @endif
</div>

{{-- Include Modals --}}
@include('admin.users.partials.modal-create')
@foreach($users as $user)
    @include('admin.users.partials.modal-edit', ['user' => $user])
@endforeach

@endsection

@push('scripts')
<script>
    // Fungsi konfirmasi hapus menggunakan SweetAlert
    function confirmDelete(userId) {
        Swal.fire({
            title: 'Anda Yakin?',
            text: "Data pengguna yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    }

    // Fungsi tunggal untuk preview foto di modal manapun
    function previewPhoto(event) {
        const input = event.target;
        const [file] = input.files;
        if (file) {
            // Dapatkan target preview dari data attribute
            const previewTarget = document.querySelector(input.dataset.previewTarget);
            const previewContainer = document.querySelector(input.dataset.previewContainer);

            if (previewTarget) {
                previewTarget.src = URL.createObjectURL(file);
            }
            if (previewContainer) {
                previewContainer.style.display = 'block';
            }
        }
    }

    // Terapkan event listener ke semua input file dengan class 'photo-preview-input'
    document.querySelectorAll('.photo-preview-input').forEach(input => {
        input.addEventListener('change', previewPhoto);
    });
</script>
@endpush