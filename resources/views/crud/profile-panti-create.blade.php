@extends('layouts.app')

@section('title', 'Tambah Data Anak')
@section('header-title', 'Tambah Data Anak')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Formulir Data Anak Baru</h5>
    </div>
    <div class="card-body">
        <form action="{{ route(auth()->user()->role . '.profile.panti.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nim" class="form-label">NIK</label>
                            <input type="text" class="form-control @error('nim') is-invalid @enderror" id="nim" name="nim" value="{{ old('nim') }}" pattern="\d{16}" title="NIK harus 16 digit angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <small class="form-text text-muted">Masukkan 16 digit angka jika ada.</small>
                            @error('nim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="" disabled selected>Pilih...</option>
                                <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sekolah" class="form-label">Sekolah</label>
                            <input type="text" class="form-control @error('sekolah') is-invalid @enderror" id="sekolah" name="sekolah" value="{{ old('sekolah') }}">
                            @error('sekolah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                {{-- Kolom Kanan --}}
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Foto / Pindai Wajah (Untuk Dataset) <span class="text-danger">*</span></label>
                        
                        <!-- Area Video / Preview -->
                        <div class="position-relative mb-2">
                            <video id="webcam" autoplay playsinline style="width: 100%; object-fit: cover; border-radius: 8px; display: none;"></video>
                            <img id="photo-preview" src="{{ asset('images/default-avatar.png') }}" alt="preview" class="img-thumbnail" style="width:100%; object-fit: cover; border-radius: 8px;">
                            <canvas id="canvas" style="display: none;"></canvas>
                        </div>
                        
                        <!-- Tombol Kontrol Kamera -->
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary" id="btn-start-camera">
                                Buka Kamera Web
                            </button>
                            <button type="button" class="btn btn-success" id="btn-capture" style="display: none;">
                                Ambil Foto Wajah
                            </button>
                            <button type="button" class="btn btn-danger" id="btn-stop-camera" style="display: none;">
                                Tutup Kamera
                            </button>
                        </div>

                        <div class="text-center mb-2">atau upload foto secara manual:</div>
                        <input class="form-control @error('photo') is-invalid @enderror" type="file" id="photo" name="photo" accept="image/*">
                        <input type="hidden" name="photo_base64" id="photo_base64">
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <a href="{{ route(auth()->user()->role . '.profile.panti') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Logika Upload File Biasa
document.getElementById('photo').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('photo-preview').src = URL.createObjectURL(file);
        document.getElementById('photo-preview').style.display = 'block';
        document.getElementById('webcam').style.display = 'none';
        document.getElementById('photo_base64').value = ''; // Hapus base64 jika pilih file manual
    }
});

// Logika Kamera (Webcam)
const webcamElement = document.getElementById('webcam');
const canvasElement = document.getElementById('canvas');
const btnStart = document.getElementById('btn-start-camera');
const btnCapture = document.getElementById('btn-capture');
const btnStop = document.getElementById('btn-stop-camera');
const photoPreview = document.getElementById('photo-preview');
const base64Input = document.getElementById('photo_base64');
let stream = null;

btnStart.addEventListener('click', async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        webcamElement.srcObject = stream;
        
        webcamElement.style.display = 'block';
        photoPreview.style.display = 'none';
        
        btnStart.style.display = 'none';
        btnCapture.style.display = 'block';
        btnStop.style.display = 'block';
    } catch (err) {
        alert("Gagal mengakses kamera: " + err);
    }
});

btnStop.addEventListener('click', () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    webcamElement.style.display = 'none';
    photoPreview.style.display = 'block';
    
    btnStart.style.display = 'block';
    btnCapture.style.display = 'none';
    btnStop.style.display = 'none';
});

btnCapture.addEventListener('click', () => {
    // Sesuaikan ukuran canvas dengan resolusi video
    canvasElement.width = webcamElement.videoWidth;
    canvasElement.height = webcamElement.videoHeight;
    
    // Capture frame
    const context = canvasElement.getContext('2d');
    context.drawImage(webcamElement, 0, 0, canvasElement.width, canvasElement.height);
    
    // Convert ke format base64
    const dataUrl = canvasElement.toDataURL('image/jpeg', 0.9);
    
    // Tampilkan ke user
    photoPreview.src = dataUrl;
    base64Input.value = dataUrl;
    
    // Hapus input tipe file jika user memilih kamera
    document.getElementById('photo').value = '';
    
    // Tutup kamera otomatis
    btnStop.click();
});
</script>
@endpush