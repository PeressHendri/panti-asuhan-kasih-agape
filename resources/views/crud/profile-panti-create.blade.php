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
                        <label for="photo" class="form-label">Foto</label>
                        <img id="photo-preview" src="{{ asset('images/default-avatar.png') }}" alt="preview" class="img-thumbnail mb-2" style="width:100%; object-fit: cover;">
                        <input class="form-control @error('photo') is-invalid @enderror" type="file" id="photo" name="photo" accept="image/*">
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
// Skrip untuk preview foto saat file dipilih
document.getElementById('photo').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('photo-preview').src = URL.createObjectURL(file);
    }
});
</script>
@endpush