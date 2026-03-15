@extends('layouts.app')

@section('title', 'Tambah Galeri')
@section('header-title', 'Tambah Foto Galeri')

@section('content')
<div class="row w-100 m-0">
    <div class="col-lg-8 mx-auto p-0">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold">Form Tambah Foto Galeri</h5>
            </div>
            <div class="card-body p-4">

                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route($routePrefix . '.gallery.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="image" class="form-label fw-bold">Pilih Foto / Video <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*,video/mp4,video/webm,video/ogg" required>
                        <small class="text-muted">Maksimal ukuran file adalah 20MB (Video) atau 5MB (Foto).</small>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Foto (Opsional)</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="Kegiatan Belajar..." maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi singkat foto ini...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route($routePrefix . '.gallery.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
