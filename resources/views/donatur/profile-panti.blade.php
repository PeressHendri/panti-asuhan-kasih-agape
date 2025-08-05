@extends('layouts.app')

@section('title', 'Profil Panti - Donatur')
@section('header-title', 'Profil Panti Asuhan Kasih Agape')

@push('styles')
<style>
    .panti-profile-card {
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .panti-profile-card h5 {
        color: #2c3e50;
        font-weight: 600;
        border-left: 4px solid #3498db;
        padding-left: 10px;
        margin-top: 1.5rem;
    }
    .child-gallery-card {
        text-align: center;
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .child-gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .child-gallery-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .child-gallery-card .card-body {
        padding: 1rem;
    }
</style>
@endpush

@section('content')

{{-- Informasi Panti --}}
<div class="card panti-profile-card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h3 class="mb-3">Tentang Panti Asuhan Kasih Agape</h3>
                <p><strong>Alamat:</strong> Jl. Pakis Gunung I/133 B, Surabaya</p>
                <p><strong>Kontak:</strong> 0812 3166 3336 / 0813 3130 7503</p>
                <hr>
                <h5>Visi</h5>
                <p>Mengasuh, mendidik, dan membangun generasi muda seutuhnya yang hidup takut akan Tuhan, serta menjadikan mereka generasi yang bertanggung jawab dengan masa depan yang cerah.</p>
                <h5>Misi</h5>
                <ul>
                    <li>Memberikan pendidikan rohani, formal, dan informal sebagai bekal hidup agar menjadi pribadi yang berintegritas dan tangguh.</li>
                    <li>Membentuk karakter dan etika kepribadian yang baik untuk kemuliaan nama Tuhan.</li>
                </ul>
            </div>
            
            {{-- BAGIAN YANG DIUBAH --}}
            <div class="col-lg-5 d-flex justify-content-center align-items-center">
                <img src="{{ asset('assets/img/logoagape.png') }}"
                     class="rounded shadow-sm"
                     alt="Logo Panti Asuhan Kasih Agape"
                     style="max-width: 80%; height: auto;">
            </div>
            {{-- AKHIR BAGIAN YANG DIUBAH --}}

        </div>
    </div>
</div>

{{-- Galeri Anak Panti --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Galeri Anak-Anak Panti</h5>
    </div>
    <div class="card-body">
        @if($children->isEmpty())
            <div class="text-center text-muted p-5">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <p>Data anak-anak panti belum tersedia.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($children as $child)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="child-gallery-card">
                        <img src="{{ $child->photo ? asset('storage/' . $child->photo) : asset('images/default-avatar.png') }}" alt="{{ $child->nama }}">
                        <div class="card-body">
                            <h6 class="card-title mb-0">{{ $child->nama }}</h6>
                            <p class="card-text text-muted">
                                {{ $child->tanggal_lahir ? $child->tanggal_lahir->age . ' tahun' : 'Umur tidak diketahui' }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection