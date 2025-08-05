@extends('layouts.app')

@section('title', 'Monitoring CCTV')
@section('header-title', 'Monitoring CCTV')

@section('content')

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header" style="background-color: var(--card-header-bg);">
                <h5 class="mb-0" style="color: var(--heading-color);">Live Monitoring CCTV</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="camera-container mb-4">
                            <div class="camera-placeholder"
                                style="background-color: var(--camera-bg); border: 1px solid var(--border-color);">
                                <div class="camera-feed bg-secondary rounded"
                                    style="height: 400px; display: flex; align-items: center; justify-content: center; background-color: var(--camera-feed-bg);">
                                    <video width="100%" height="400" controls autoplay
                                        style="background:#000; border-radius:8px;">
                                        <source src="http://ip-camera-address/live/stream.m3u8"
                                            type="application/x-mpegURL">
                                        Browser Anda tidak mendukung video tag.
                                    </video>
                                </div>
                                <div class="camera-controls mt-3 d-flex justify-content-center">
                                    <button class="btn btn-primary mx-2">
                                        <i class="fas fa-search-plus"></i> Zoom In
                                    </button>
                                    <button class="btn btn-primary mx-2">
                                        <i class="fas fa-search-minus"></i> Zoom Out
                                    </button>
                                    <button class="btn btn-success mx-2">
                                        <i class="fas fa-camera"></i> Capture
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="camera-list">
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action active">
                                    <div class="d-flex justify-content-between">
                                        <strong style="color: var(--text-color);">Ruang Tamu</strong>
                                        <span class="badge bg-success"
                                            style="color: var(--badge-text-color); background-color: var(--badge-success-bg);">Aktif</span>
                                    </div>
                                    <small style="color: var(--muted-text-color);">Kamera utama</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <strong style="color: var(--text-color);">Halaman Depan</strong>
                                        <span class="badge bg-success"
                                            style="color: var(--badge-text-color); background-color: var(--badge-success-bg);">Aktif</span>
                                    </div>
                                    <small style="color: var(--muted-text-color);">Area bermain</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .camera-container {
            position: relative;
        }

        .camera-placeholder {
            background-color: var(--camera-bg, #f8f9fa);
            border: 1px solid var(--border-color, #dee2e6);
        }

        .list-group-item.active {
            background-color: var(--list-group-active-bg, #007bff);
            border-color: var(--list-group-active-border, #007bff);
            color: var(--list-group-active-text, #ffffff);
        }

        .list-group-item.active small {
            color: var(--list-group-active-muted, #e0e0e0);
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Simulasi switch kamera
            $('.list-group-item').click(function (e) {
                e.preventDefault();
                $('.list-group-item').removeClass('active');
                $(this).addClass('active');
                var cameraName = $(this).find('strong').text();
                $('.camera-placeholder h4').text('Live Feed: ' + cameraName);
            });
        });
    </script>
@endsection