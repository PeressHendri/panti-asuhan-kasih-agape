@extends('layouts.app')

@section('title', 'Data Pengasuh')
@section('header-title', 'Data Pengasuh')

@section('content')
<div class="container-fluid p-4 text-light">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex align-items-center">
                    <h5 class="mb-0">Data Pengasuh</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No. Telepon</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengasuhList as $pengasuh)
                                    <tr>
                                        <td>
                                            @if($pengasuh->photo)
                                                <img src="{{ asset('storage/' . $pengasuh->photo) }}" alt="Foto" width="48" height="48" style="object-fit:cover;object-position:center;border-radius:50%;border:2px solid #ffd200;">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($pengasuh->name) }}&background=ffd200&color=222&size=48" alt="Foto" width="48" height="48" style="object-fit:cover;object-position:center;border-radius:50%;border:2px solid #ffd200;">
                                            @endif
                                        </td>
                                        <td>{{ $pengasuh->name }}</td>
                                        <td>{{ $pengasuh->email }}</td>
                                        <td>{{ $pengasuh->phone ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada data pengasuh.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 