<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel{{ $user->id }}">Edit Pengguna: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name{{ $user->id }}" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name{{ $user->id }}" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_email{{ $user->id }}" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_email{{ $user->id }}" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone{{ $user->id }}" class="form-label">Nomor Handphone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="edit_phone{{ $user->id }}" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_password{{ $user->id }}" class="form-label">Password Baru</label>
                        <input type="password" name="password" id="edit_password{{ $user->id }}" class="form-control @error('password') is-invalid @enderror">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_role{{ $user->id }}" class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="edit_role{{ $user->id }}" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pengasuh" {{ old('role', $user->role) === 'pengasuh' ? 'selected' : '' }}>Pengasuh</option>
                            <option value="donatur" {{ old('role', $user->role) === 'donatur' ? 'selected' : '' }}>Donatur</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti Foto Profil</label>
                        <input type="file" name="photo" class="form-control photo-preview-input @error('photo') is-invalid @enderror"
                               data-preview-target="#edit-photo-preview-{{ $user->id }}"
                               data-preview-container="#edit-photo-preview-container-{{ $user->id }}">
                        @if($user->photo)
                            <div class="mt-2">
                                <small class="text-muted">Foto saat ini:</small><br>
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto" width="80" class="mt-1 rounded">
                            </div>
                        @endif
                         <div class="mt-2" id="edit-photo-preview-container-{{ $user->id }}" style="display:none;">
                            <small class="text-muted">Foto baru:</small><br>
                            <img id="edit-photo-preview-{{ $user->id }}" width="80" class="mt-1 rounded">
                        </div>
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>