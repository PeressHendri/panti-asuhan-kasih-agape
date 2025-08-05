<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_name" class="form-label">Nama</label>
                        <input type="text" name="name" id="create_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" name="email" id="create_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_phone" class="form-label">No. Handphone</label>
                        <input type="text" name="phone" id="create_phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_password" class="form-label">Password</label>
                        <input type="password" name="password" id="create_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="create_password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_role" class="form-label">Role</label>
                        <select name="role" id="create_role" class="form-select" required>
                            <option value="" selected disabled>Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="pengasuh">Pengasuh</option>
                            <option value="donatur">Donatur</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_photo" class="form-label">Foto Profil</label>
                        <input type="file" name="photo" class="form-control photo-preview-input" 
                               data-preview-target="#create-photo-preview"
                               data-preview-container="#create-photo-preview-container">
                        <div class="mt-2" id="create-photo-preview-container" style="display:none;">
                            <small>Preview:</small><br>
                            <img id="create-photo-preview" width="80" class="mt-1 rounded">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>