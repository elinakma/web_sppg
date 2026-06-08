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
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4 class="mb-0 fw-bold">Kelola Pengguna</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Search -->
                    <form action="{{ route('admin.pengguna.index') }}" method="GET" class="d-flex align-items-center gap-2 filter-box">
                        <input type="text"
                            name="search"
                            class="form-control form-control-sm rounded-pill filter-input"
                            placeholder="Nama / email / role..."
                            value="{{ request('search') }}">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="submit">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </form>

                    <!-- Tombol Tambah -->
                    <button type="button"
                        class="btn btn-primary rounded-pill px-3 shadow-sm"
                        style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;"
                        data-bs-toggle="modal"
                        data-bs-target="#tambahPenggunaModal">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Pengguna
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th width="100">Kirim APK</th>
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
                            <td class="text-center">
                                <span class="badge role-badge d-inline-block role-{{ strtolower($user->role) }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge status-badge d-inline-block {{ $user->status === 'Aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($user->telepon)
                                    @php
                                        $nomorWA = '62' . ltrim($user->telepon, '0');
                                        $linkAPK = 'https://drive.google.com/drive/folders/1SGdkDW41UNo5PObMdP8l1nSvxahsEko9?usp=sharing';
                                        $pesan = urlencode("Halo {$user->name}, berikut link instalasi aplikasi mobile:\n{$linkAPK}");
                                    @endphp
                                    <div class="d-flex justify-content-center">
                                        <a href="https://wa.me/{{ $nomorWA }}?text={{ $pesan }}"
                                        target="_blank"
                                        class="soft-btn btn-wa"
                                        title="Kirim Link APK via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-group">
                                    <!-- Edit -->
                                    <button type="button"
                                        class="soft-btn btn-detail edit-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editPenggunaModal"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-telepon="{{ $user->telepon }}"
                                        data-role="{{ $user->role }}"
                                        data-title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button"
                                        class="soft-btn btn-delete btn-delete-trigger"
                                        data-id="{{ $user->id }}"
                                        data-title="Hapus"
                                        data-nama="{{ $user->name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    <form id="delete-form-{{ $user->id }}"
                                        action="{{ route('admin.pengguna.destroy', $user) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
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
                    Menampilkan <span class="badge bg-primary">{{ $users->firstItem() }}</span>
                    –
                    <span class="badge bg-primary">{{ $users->lastItem() }}</span>
                    dari
                    <span class="badge bg-secondary">{{ $users->total() }}</span>
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
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-person-badge me-1"></i>Tambah Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('admin.pengguna.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Pengguna</label>
                                <input type="text" name="name"
                                    class="form-control shadow-sm @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                    class="form-control shadow-sm @error('email') is-invalid @enderror"
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
                                        class="form-control shadow-sm @error('telepon') is-invalid @enderror"
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
                                    <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin/Kepala SPPG</option>
                                    <option value="Aslap" {{ old('role') == 'Aslap' ? 'selected' : '' }}>Asisten Lapangan</option>
                                    <option value="Gizi" {{ old('role') == 'Gizi' ? 'selected' : '' }}>Ahli Gizi</option>
                                    <option value="Akuntan" {{ old('role') == 'Akuntan' ? 'selected' : '' }}>Akuntansi</option>
                                    <option value="Driver" {{ old('role') == 'Driver' ? 'selected' : '' }}>Driver</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control shadow-sm @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control shadow-sm" required>
                            </div>
                        </div>
                    </div>
        
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"
                            style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;">
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
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-person-badge me-1"></i>Edit Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Pengguna</label>
                                <input type="text" name="name" class="form-control shadow-sm @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control shadow-sm @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <div class="input-telp">
                                    <span class="input-telp-text">+62</span>
                                    <input type="text" name="telepon" class="form-control shadow-sm @error('telepon') is-invalid @enderror"
                                        placeholder="859xxxxxxxx" pattern="[8-9][0-9]{8,12}">
                                </div>
                                @error('telepon') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select shadow-sm @error('role') is-invalid @enderror" required>
                                    <option value="">Pilih Role</option>
                                    <option value="Admin">Admin/Kepala SPPG</option>
                                    <option value="Aslap">Asisten Lapangan</option>
                                    <option value="Gizi">Ahli Gizi</option>
                                    <option value="Akuntan">Akuntansi</option>
                                    <option value="Driver">Driver</option>
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select shadow-sm">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password (kosongkan jika tidak ingin ubah)</label>
                                <input type="password" name="password" class="form-control shadow-sm @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"
                            style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;">
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
        <h5 class="fw-bold mt-3">Sukses</h5>
        <p class="text-muted mb-0">
            {{ session('success') }}
        </p>
    </div>
</div>
@endif

<!-- overlay error -->
@if(session('error'))
<div class="error-overlay">
    <div class="error-card">
        <div class="error-icon">
            <i class="bi bi-x-lg"></i>
        </div>
        <h5 class="fw-bold mt-3">Gagal</h5>
        <p class="text-muted mb-0">
            {{ session('error') }}
        </p>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    const successOverlay = document.querySelector('.success-overlay');
    if (successOverlay) {
        setTimeout(() => {
            successOverlay.classList.add('show');
        }, 50);

        setTimeout(() => {
            successOverlay.classList.add('hide');

            setTimeout(() => {
                successOverlay.remove();
            }, 300);
        }, 3000);
    }

    const errorOverlay = document.querySelector('.error-overlay');
    if (errorOverlay) {
        setTimeout(() => {
            errorOverlay.classList.add('show');
        }, 50);

        setTimeout(() => {
            errorOverlay.classList.add('hide');

            setTimeout(() => {
                errorOverlay.remove();
            }, 300);
        }, 3000);
    }

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editForm');

            form.action = '{{ route("admin.pengguna.update", ":id") }}'.replace(':id', this.dataset.id);

            form.querySelector('[name="name"]').value = this.dataset.name;
            form.querySelector('[name="email"]').value = this.dataset.email;
            form.querySelector('[name="telepon"]').value = this.dataset.telepon ?? '';
            form.querySelector('[name="role"]').value = this.dataset.role;

            form.querySelector('[name="password"]').value = '';
            form.querySelector('[name="password_confirmation"]').value = '';
        });
    });

    let deleteId = null;

    const overlay = document.getElementById('deleteConfirmOverlay');
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');
    const message = document.getElementById('deleteMessage');

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            deleteId = this.dataset.id;
            message.innerHTML = `Yakin hapus pengguna ini?`;
            overlay.classList.add('show');
        });
    });

    cancelBtn.onclick = () => {
        overlay.classList.add('hide');

        setTimeout(() => {
            overlay.classList.remove('show', 'hide');
        }, 300);

        deleteId = null;
    };

    confirmBtn.onclick = () => {
        if (deleteId) {
            document.getElementById(`delete-form-${deleteId}`).submit();
        }
    };

});
</script>
@endsection