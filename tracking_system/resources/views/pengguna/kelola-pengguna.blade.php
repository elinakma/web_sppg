@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none fw-semibold" style="color: #133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page">
                        Kelola Pengguna
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">
            
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <h4 class="mb-0 fw-bold">Kelola Pengguna</h4>

                <div class="d-flex gap-2">
                    <!-- Search -->
                    <form action="{{ route('admin.pengguna.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text"
                                name="search"
                                class="form-control"
                                placeholder="Cari nama / email / role..."
                                value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Tambah -->
                    <button type="button"
                        class="btn"
                        style="background-color: #133b84; color: white;"
                        data-bs-toggle="modal"
                        data-bs-target="#tambahPenggunaModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Pengguna
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $key => $user)
                        <tr>
                            <td class="text-center">
                                {{ $users->firstItem() + $key }}
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">
                                {{ $user->telepon ?? '-' }}
                            </td>
                            <td style="text-align: center;">
                                <span class="role-badge role-{{ strtolower($user->role) }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span id="status-text-{{ $user->id }}" 
                                    class="badge {{ $user->status === 'Aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $user->status }}
                                </span>
                                <!-- Toggle Status -->
                                <label class="switch">
                                    <input type="checkbox" 
                                        class="toggle-status" 
                                        data-id="{{ $user->id }}" 
                                        {{ $user->status === 'Aktif' ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <!-- Edit -->
                                <button type="button" 
                                    class="btn btn-sm btn-warning action-btn edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editPenggunaModal" 
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-telepon="{{ $user->telepon }}"
                                    data-role="{{ $user->role }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <!-- Hapus -->
                                <form id="delete-form-{{ $user->id }}"
                                    action="{{ route('admin.pengguna.destroy', $user) }}"
                                    method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-sm btn-danger action-btn btn-delete"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada data pengguna
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan
                    <strong>{{ $users->firstItem() }}</strong>
                    –
                    <strong>{{ $users->lastItem() }}</strong>
                    dari
                    <strong>{{ $users->total() }}</strong>
                    pengguna
                </div>

                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pengguna -->
    <div class="modal fade" id="tambahPenggunaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('admin.pengguna.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Pengguna</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">+62</span>
                                    <input type="text" name="telepon"
                                        class="form-control @error('telepon') is-invalid @enderror"
                                        placeholder="859xxxxxxxx"
                                        value="{{ old('telepon') }}"
                                        pattern="[8-9][0-9]{8,12}"
                                        title="Nomor dimulai dengan 8 atau 9, tanpa 0 di depan, 9-13 digit">
                                </div>
                                <small class="form-text text-muted">Contoh: 85940123456 (tanpa 0 di awal)</small>
                                @error('telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Pilih Role</option>
                                    <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="Aslap" {{ old('role') == 'Aslap' ? 'selected' : '' }}>Asisten Lapangan</option>
                                    <option value="Gizi" {{ old('role') == 'Gizi' ? 'selected' : '' }}>Ahli Gizi</option>
                                    <option value="Akuntan" {{ old('role') == 'Akuntan' ? 'selected' : '' }}>Akuntansi</option>
                                    <option value="Driver" {{ old('role') == 'Driver' ? 'selected' : '' }}>Distribusi</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn" style="background-color:#133b84;color:white">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pengguna -->
    <div class="modal fade" id="editPenggunaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Pengguna</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">+62</span>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
                                        placeholder="859xxxxxxxx" pattern="[8-9][0-9]{8,12}">
                                </div>
                                @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Pilih Role</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Aslap">Asisten Lapangan</option>
                                    <option value="Gizi">Ahli Gizi</option>
                                    <option value="Akuntan">Akuntansi</option>
                                    <option value="Driver">Distribusi</option>
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password (kosongkan jika tidak ingin ubah)</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn" style="background-color:#133b84;color:white">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Overlay Konfirmasi Hapus -->
    <div class="confirm-overlay" id="deleteConfirmOverlay">
        <div class="confirm-card">
            <div class="confirm-icon">
                <i class="bi bi-exclamation-lg"></i>
            </div>
            <h5 class="fw-bold mt-3">Konfirmasi Hapus</h5>
            <p class="text-muted mb-4" id="deleteMessage">
                Apakah yakin ingin menghapus data ini?
            </p>

            <div class="d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-secondary px-4" id="cancelDelete">
                    Tidak
                </button>
                <button type="button" class="btn btn-danger px-4" id="confirmDelete">
                    Ya
                </button>
            </div>
        </div>
    </div>
</div>

<!-- overlay sukses -->
@if(session('success'))
<div class="success-overlay">
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h5 class="fw-bold mt-3">Success</h5>
        <p class="text-muted mb-0">
            {{ session('success') }}
        </p>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Input data modal edit
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = document.getElementById('editForm');

            form.action = '{{ route("admin.pengguna.update", ":id") }}'.replace(':id', this.dataset.id);

            form.querySelector('input[name="name"]').value   = this.dataset.name;
            form.querySelector('input[name="email"]').value  = this.dataset.email;
            form.querySelector('input[name="telepon"]').value = this.dataset.telepon ?? '';
            form.querySelector('select[name="role"]').value  = this.dataset.role;

            // Kosongkan password
            form.querySelector('input[name="password"]').value            = '';
            form.querySelector('input[name="password_confirmation"]').value = '';
        });
    });

    // Konfirmasi hapus
    let deleteId = null;
    const deleteOverlay = document.getElementById('deleteConfirmOverlay');
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');
    const message = document.getElementById('deleteMessage');

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            deleteId = this.dataset.id;
            const name = this.dataset.name;

            message.innerHTML = `Apakah yakin ingin menghapus <strong>${name}</strong>?`;
            deleteOverlay.classList.add('show');
        });
    });

    cancelBtn.addEventListener('click', () => {
        deleteOverlay.classList.remove('show');
        deleteId = null;
    });

    confirmBtn.addEventListener('click', () => {
        if (deleteId) {
            document.getElementById(`delete-form-${deleteId}`).submit();
        }
    });

    // overlay sukses otomatis hilang
    const overlay = document.querySelector('.success-overlay');
    if (overlay) {
        setTimeout(() => {
            overlay.classList.add('hide');
            setTimeout(() => {
                overlay.remove();
            }, 500);
        }, 3000);
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function(e) {

            const userId = this.dataset.id;
            const isActive = this.checked;
            const statusText = document.getElementById(`status-text-${userId}`);

            console.log(`%c🔄 Toggle diklik - User ID: ${userId} | Mau jadi: ${isActive ? 'Aktif' : 'Nonaktif'}`, 'color: orange');

            if (!confirm(`Ubah status menjadi ${isActive ? 'Aktif' : 'Nonaktif'}?`)) {
                this.checked = !isActive;
                return;
            }

            console.log('📤 Mengirim request PATCH...');

            fetch(`/admin/pengguna/${userId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? 
                                   document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                body: JSON.stringify({
                    status: isActive ? 'Aktif' : 'Nonaktif'
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    statusText.textContent = data.status;
                    statusText.className = `badge ${data.status === 'Aktif' ? 'bg-success' : 'bg-secondary'}`;
                    console.log('🎉 UI berhasil diupdate');
                } else {
                    alert(data.message || 'Gagal mengubah status');
                    this.checked = !isActive;
                }
            })
            .catch(error => {
                alert('Gagal mengirim data. Lihat Console (F12) untuk detail lengkap.');
                this.checked = !isActive;
            });
        });
    });
});
</script>
@endsection