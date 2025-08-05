@extends('layouts.app')
@section('title', 'Edit Profil Admin')
@section('header-title', 'Edit Profil Admin')
@section('content')
    <div class="container py-4">
        <h2 class="dashboard-title">Edit Profil Admin</h2>
        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-12 col-md-6">
                <label class="dashboard-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="dashboard-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="dashboard-label">Password Baru (opsional)</label>
                <input type="password" name="password" class="form-control">
                <small class="dashboard-label">Kosongkan jika tidak ingin ganti password</small>
            </div>
            <div class="col-12 col-md-6">
                <label class="dashboard-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="col-12">
                <label class="dashboard-label" style="color: var(--text-color);">Foto Profil</label>
                <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(event)">
                <small class="text-danger"><span style="color:red">* </span>Ukuran maksimal 10MB. Format: jpg, jpeg, png,
                    dll.</small>
                @if($user->photo)
                    <div class="mt-2 text-center">
                        <small style="color: var(--muted-text-color,rgb(99, 99, 99));">Foto saat ini:</small><br>
                        <img src="{{ asset('storage/' . $user->photo) }}" class="img-fluid rounded-circle profile-image shadow"
                            style="width: 170px; height: 170px; border: 2px solid var(--border-color); object-fit: cover; object-position: center;"
                            alt="Foto Profil" id="old-photo-preview">
                    </div>
                @endif
                <div class="mt-2 text-center" id="new-photo-preview-container" style="display:none;">
                    <small style="color: var(--muted-text-color, #cccccc);">Foto baru:</small><br>
                    <img id="new-photo-preview" class="img-fluid rounded-circle profile-image shadow"
                        style="width: 170px; height: 170px; border: 2px solid var(--border-color); object-fit: cover; object-position: center;">
                </div>
            </div>
            <div class="col-12">
                <button type="button" class="btn btn-primary" id="btn-simpan-profil">Simpan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('btn-simpan-profil').addEventListener('click', function (e) {
            var pass = document.querySelector('input[name="password"]').value;
            var passConf = document.querySelector('input[name="password_confirmation"]').value;
            if (pass && pass !== passConf) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Tidak Sama',
                    text: 'Password dan konfirmasi password harus sama!',
                    confirmButtonText: 'OK'
                });
                return;
            }
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menyimpan perubahan profil?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.closest('form').submit();
                }
            });
        });

        function previewPhoto(event) {
            const [file] = event.target.files;
            if (file) {
                const preview = document.getElementById('new-photo-preview');
                preview.src = URL.createObjectURL(file);
                document.getElementById('new-photo-preview-container').style.display = 'block';
            }
        }
    </script>
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = "{{ $dashboard }}";
            });
        </script>
    @endif
@endpush