@if(auth()->check() && auth()->user()->role === 'admin')
    <div class="modal fade" id="modalKamera" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.cctv.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-video me-2"></i>Tambah Kamera</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ID Kamera</label>
                            <input type="text" name="kamera_id" class="form-control" placeholder="Contoh: cam_halaman" required>
                            <small class="text-muted">ID unik untuk endpoint API.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Tampilan</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Kamera Halaman Depan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">URL HLS (Stream Browser)</label>
                            <input type="url" name="hls_url" class="form-control" placeholder="https://domain.com/stream.m3u8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">URL RTSP Mentah</label>
                            <input type="text" name="rtsp_url" class="form-control" placeholder="rtsp://admin:pass@ip:port/stream">
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" checked>
                            <label class="form-check-label fw-bold" for="isActiveCheck">Kamera Aktif Ditampilkan?</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach($cameras as $camera)
        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditCam{{ $camera->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('admin.cctv.update', $camera->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-dark text-white border-0">
                            <h5 class="modal-title font-weight-bold"><i class="fas fa-edit me-2"></i>Edit Kamera</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">ID Kamera</label>
                                <input type="text" class="form-control bg-light" value="{{ $camera->kamera_id }}" readonly disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Tampilan</label>
                                <input type="text" name="nama" class="form-control" value="{{ $camera->nama }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">URL HLS (Stream Browser)</label>
                                <input type="url" name="hls_url" class="form-control" value="{{ $camera->hls_url }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">URL RTSP Mentah</label>
                                <input type="text" name="rtsp_url" class="form-control" value="{{ $camera->rtsp_url }}">
                            </div>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck{{ $camera->id }}" {{ $camera->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="isActiveCheck{{ $camera->id }}">Aktifkan Kamera</label>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-4">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Delete -->
        <div class="modal fade" id="modalDeleteCam{{ $camera->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('admin.cctv.destroy', $camera->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content border-0 shadow text-center">
                        <div class="modal-body p-5">
                            <i class="fas fa-trash-alt text-danger fa-4x mb-4"></i>
                            <h4 class="fw-bold">Hapus Kamera?</h4>
                            <p class="text-muted">Apakah Anda yakin ingin menghapus kamera <strong>{{ $camera->nama }}</strong>?</p>
                            <div class="mt-5 d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger px-4">Ya, Hapus</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif
