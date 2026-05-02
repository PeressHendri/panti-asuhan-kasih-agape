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
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="dashboard-label">Kosongkan jika tidak ingin ganti password</small>
            </div>
            <div class="col-12 col-md-6">
                <label class="dashboard-label">Konfirmasi Password Baru</label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
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
            
            {{-- Switch Mode Absensi --}}
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm p-3" style="background: #f0f7ff; border-radius: 12px; border-left: 4px solid #3b82f6 !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="fw-bold mb-1" style="color: var(--text-color);">
                                <i class="fas fa-toggle-on text-primary me-2"></i>Mode Absensi
                            </div>
                            <small class="text-muted d-block">
                                <span class="text-success fw-semibold">Otomatis</span>: Absensi dicatat langsung oleh kamera Face Recognition (Raspberry Pi).<br>
                                <span class="text-warning fw-semibold">Manual</span>: Admin/Pengasuh dapat menambah, mengedit, dan check-out kehadiran secara manual dari dashboard.
                            </small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted" id="modeLabel">
                                {{ \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) ? 'Mode: Manual' : 'Mode: Otomatis' }}
                            </span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" style="cursor: pointer; width: 3em; height: 1.5em;"
                                    type="checkbox" role="switch"
                                    id="enable_manual_attendance"
                                    name="enable_manual_attendance"
                                    value="1"
                                    {{ \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) ? 'checked' : '' }}
                                    onchange="document.getElementById('modeLabel').textContent = this.checked ? 'Mode: Manual' : 'Mode: Otomatis'">
                                <label class="form-check-label" for="enable_manual_attendance" style="cursor: pointer; color: var(--text-color);">
                                    Aktifkan Manual
                                </label>
                            </div>
                        </div>
                    </div>
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

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session("success") }}',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = "{{ $dashboard }}";
            });
        </script>
    @endif
@endpush