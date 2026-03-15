@extends('layouts.app')

@section('title', 'Edit Galeri')
@section('header-title', 'Edit Foto Galeri')

@section('content')
<div class="row w-100 m-0">
    <div class="col-lg-8 mx-auto p-0">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold">Form Edit Foto Galeri</h5>
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

                <form action="{{ route($routePrefix . '.gallery.update', $gallery->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 text-center">
                        <label class="form-label d-block text-start fw-bold">File Saat Ini:</label>
                        @php
                            $extension = pathinfo($gallery->image, PATHINFO_EXTENSION);
                            $isVideo = in_array(strtolower($extension), ['mp4', 'webm', 'ogg']);
                        @endphp
                        @if($isVideo)
                            <video src="{{ Storage::url($gallery->image) }}" class="img-thumbnail w-50" controls></video>
                        @else
                            <img src="{{ Storage::url($gallery->image) }}" class="img-thumbnail w-50" alt="Galeri">
                        @endif
                        <div class="mt-2 text-start">
                            <label for="image" class="form-label fw-bold mt-2">Ubah File Foto / Video (Opsional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*,video/mp4,video/webm,video/ogg">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah. Maksimal 20MB (Video) / 5MB (Foto).</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Foto (Opsional)</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $gallery->title) }}" placeholder="Kegiatan Belajar..." maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi singkat foto ini...">{{ old('description', $gallery->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route($routePrefix . '.gallery.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Perbarui
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
