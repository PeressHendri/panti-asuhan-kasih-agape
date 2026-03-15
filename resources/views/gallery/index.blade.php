@extends('layouts.app')

@section('title', 'Galeri')
@section('header-title', 'Galeri')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">Daftar Foto Galeri</h5>
        <a href="{{ route($routePrefix . '.gallery.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Foto
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Foto</th>
                        <th width="20%">Judul</th>
                        <th width="35%">Deskripsi</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($galleries as $index => $gallery)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @php
                                $extension = pathinfo($gallery->image, PATHINFO_EXTENSION);
                                $isVideo = in_array(strtolower($extension), ['mp4', 'webm', 'ogg']);
                            @endphp
                            @if($isVideo)
                                <div class="position-relative d-inline-block">
                                    <video src="{{ Storage::url($gallery->image) }}" width="100" class="img-thumbnail"></video>
                                    <div class="position-absolute top-50 left-50 translate-middle">
                                        <i class="fas fa-play-circle text-white fs-4 shadow-sm"></i>
                                    </div>
                                </div>
                            @else
                                <img src="{{ Storage::url($gallery->image) }}" class="img-thumbnail" width="100" alt="Galeri">
                            @endif
                        </td>
                        <td>{{ $gallery->title ?: '-' }}</td>
                        <td class="text-start">{{ $gallery->description ?: '-' }}</td>
                        <td>
                            <a href="{{ route($routePrefix . '.gallery.edit', $gallery->id) }}" class="btn btn-sm btn-warning mb-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route($routePrefix . '.gallery.destroy', $gallery->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-image fs-1 mb-2"></i><br>
                            Belum ada foto galeri.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
