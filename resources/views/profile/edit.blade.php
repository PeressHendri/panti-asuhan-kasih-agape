@extends('layouts.app')

@section('title', 'Profil Saya')

@section('header-title', 'Profil Saya')

@section('content')
    <div class="container py-4">
        <h2 class="dashboard-title">Edit Profil</h2>
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-3">
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
            @if(auth()->user()->role === 'admin')
            {{-- Penanda bahwa form ini dari halaman admin --}}
            <input type="hidden" name="is_admin_form" value="1">
            <div class="col-12 mt-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" style="cursor: pointer; width: 3em; height: 1.5em;"
                            type="checkbox" role="switch"
                            id="enable_manual_attendance"
                            {{ \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) ? 'checked' : '' }}>
                    </div>
                    <label class="dashboard-label mb-0" for="enable_manual_attendance" style="cursor: pointer;">
                        &mdash;
                        <span id="manualStatusLabel" class="fw-bold"
                            style="color: {{ \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) ? '#22c55e' : '#94a3b8' }}">
                            {{ \Illuminate\Support\Facades\Cache::get('enable_manual_attendance', false) ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </label>
                    <span id="manualSaveIndicator" class="small text-muted ms-1" style="display:none;">
                        <i class="fas fa-check-circle text-success"></i> Tersimpan
                    </span>
                </div>
            </div>

            {{-- Threshold Akurasi AI LBPH --}}
            <div class="col-12 mt-3">
                <div class="p-3 rounded-3 border" style="background: var(--card-bg, #fff);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold" style="color: var(--text-color); font-size: 0.9rem;">
                            <i class="fas fa-sliders-h text-primary me-2"></i>Threshold Akurasi AI (LBPH)
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span id="thresholdSaveIndicator" class="small text-success" style="display:none;">
                                <i class="fas fa-check-circle"></i> Tersimpan
                            </span>
                            <span class="badge bg-primary rounded-pill px-3 py-2" id="thresholdDisplay"
                                style="font-size: 1rem; min-width: 3rem; text-align: center;">
                                {{ \Illuminate\Support\Facades\Cache::get('confidence_threshold', 75) }}
                            </span>
                        </div>
                    </div>
                    <input type="range" name="confidence_threshold" id="confidenceSlider"
                        class="threshold-slider w-100"
                        min="40" max="99" step="1"
                        value="{{ \Illuminate\Support\Facades\Cache::get('confidence_threshold', 75) }}"
                        oninput="updateSlider(this.value)">
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-success fw-semibold">40 — Longgar</small>
                        <small class="text-muted">Semakin tinggi = semakin ketat</small>
                        <small class="text-danger fw-semibold">99 — Ketat</small>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-12">
                <button type="button" class="btn btn-primary" id="btn-simpan-profil">Simpan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .threshold-slider {
            -webkit-appearance: none;
            appearance: none;
            height: 8px;
            border-radius: 99px;
            outline: none;
            cursor: pointer;
            background: linear-gradient(to right,
                #3b82f6 0%,
                #3b82f6 {{ round((\Illuminate\Support\Facades\Cache::get('confidence_threshold', 75) - 40) / 59 * 100) }}%,
                #e2e8f0 {{ round((\Illuminate\Support\Facades\Cache::get('confidence_threshold', 75) - 40) / 59 * 100) }}%,
                #e2e8f0 100%
            );
        }
        .threshold-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #3b82f6;
            box-shadow: 0 2px 6px rgba(59,130,246,0.4);
            cursor: pointer;
            transition: box-shadow 0.15s;
        }
        .threshold-slider::-webkit-slider-thumb:hover {
            box-shadow: 0 0 0 6px rgba(59,130,246,0.15);
        }
        .threshold-slider::-moz-range-thumb {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #3b82f6;
            box-shadow: 0 2px 6px rgba(59,130,246,0.4);
            cursor: pointer;
        }
    </style>
    <script>
        function updateSlider(val) {
            document.getElementById('thresholdDisplay').innerText = val;
            const pct = ((val - 40) / 59) * 100;
            document.getElementById('confidenceSlider').style.background =
                `linear-gradient(to right, #3b82f6 ${pct}%, #e2e8f0 ${pct}%)`;

            // Debounce: simpan ke server setelah 800ms berhenti geser
            clearTimeout(window._thresholdTimer);
            window._thresholdTimer = setTimeout(() => {
                fetch('{{ route("admin.set.threshold") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ threshold: parseInt(val) })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const ind = document.getElementById('thresholdSaveIndicator');
                        ind.style.display = 'inline';
                        setTimeout(() => ind.style.display = 'none', 2000);
                    }
                });
            }, 800);
        }

        // Switch Absensi Manual → AJAX langsung saat toggle
        @if(auth()->user()->role === 'admin')
        document.getElementById('enable_manual_attendance').addEventListener('change', function () {
            const enabled = this.checked;
            const lbl = document.getElementById('manualStatusLabel');
            const ind = document.getElementById('manualSaveIndicator');

            // Update label UI dulu
            lbl.textContent = enabled ? 'Aktif' : 'Tidak Aktif';
            lbl.style.color  = enabled ? '#22c55e' : '#94a3b8';

            // Kirim ke server
            fetch('{{ route("admin.toggle.manual.attendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ enabled: enabled })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    ind.style.display = 'inline';
                    setTimeout(() => ind.style.display = 'none', 2000);
                }
            })
            .catch(() => {
                // Rollback jika gagal
                this.checked = !enabled;
                lbl.textContent = !enabled ? 'Aktif' : 'Tidak Aktif';
                lbl.style.color  = !enabled ? '#22c55e' : '#94a3b8';
            });
        });
        @endif

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
                @php
                    $role = auth()->user()->role;
                    $dashboard = $role === 'sponsor' ? route('sponsor.dashboard') : ($role === 'pengasuh' ? route('pengasuh.dashboard') : route('admin.dashboard'));
                @endphp
                window.location.href = "{{ $dashboard }}";
            });
            </script>
    @endif
@endpush