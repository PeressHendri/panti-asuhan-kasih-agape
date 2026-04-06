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
            
            <div class="col-12 mt-3">
                <div class="form-check form-switch pt-2">
                    <input class="form-check-input" style="cursor: pointer;" type="checkbox" role="switch" id="enable_manual_attendance" name="enable_manual_attendance" value="1" {{ \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enable_manual_attendance" style="cursor: pointer; color: var(--text-color);">Absensi Manual</label>
                </div>
            </div>

            {{-- Pengaturan AI: Confidence Threshold --}}
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm p-3" style="background: #f8fafc; border-radius: 12px;">
                    <label class="fw-bold mb-1" style="color: var(--text-color);">
                        <i class="fas fa-sliders-h text-primary me-2"></i>Threshold Akurasi AI (LBPH)
                    </label>
                    <small class="text-muted d-block mb-2">Nilai ini menentukan seberapa ketat sistem mengenali wajah. Semakin tinggi nilainya, semakin ketat (hanya mau mengenali jika mirip sekali). <strong>Default: 75</strong></small>
                    <div class="d-flex align-items-center gap-3">
                        <input type="range" name="confidence_threshold" id="confidenceSlider"
                            class="form-range flex-grow-1"
                            min="40" max="99" step="1"
                            value="{{ $confidenceThreshold ?? 75 }}"
                            oninput="document.getElementById('thresholdDisplay').innerText = this.value">
                        <span class="badge bg-primary fs-6 px-3 py-2" id="thresholdDisplay">{{ $confidenceThreshold ?? 75 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-success">40 = Longgar (Lebih Banyak Dikenali)</small>
                        <small class="text-danger">99 = Ketat (Hanya Sangat Mirip)</small>
                    </div>
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