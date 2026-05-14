@extends('layouts.admin')

@section('title', 'Kelola Sekolah Penerima MBG')

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
                        Kelola Sekolah Penerima
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4 class="mb-0 fw-bold">Kelola Sekolah Penerima</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Search -->
                    <form action="{{ route('admin.sekolah.index') }}" method="GET" class="d-flex align-items-center gap-2 filter-box">
                        <input type="text"
                            name="search"
                            class="form-control form-control-sm rounded-pill filter-input"
                            placeholder="Cari sekolah / PIC..."
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
                        data-bs-target="#tambahSekolahModal">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Sekolah
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Sekolah</th>
                            <th>PIC</th>
                            <th>Porsi Kecil</th>
                            <th>Porsi Besar</th>
                            <th>Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sekolah as $key => $s)
                        <tr>
                            <td class="text-center">
                                {{ $sekolah->firstItem() + $key }}
                            </td>
                            <td>{{ $s->nama_sekolah }}</td>
                            <td>{{ $s->pic }}</td>
                            <td class="text-center">{{ $s->porsi_kecil_default }}</td>
                            <td class="text-center">{{ $s->porsi_besar_default }}</td>
                            <td class="text-center">
                                <span class="badge status-badge d-inline-block {{ $s->status === 'Aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-group">
                                    <!-- Edit -->
                                    <button type="button"
                                        class="soft-btn btn-detail edit-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSekolahModal"
                                        data-id="{{ $s->id }}"
                                        data-nama="{{ $s->nama_sekolah }}"
                                        data-pic="{{ $s->pic }}"
                                        data-kecil="{{ $s->porsi_kecil_default }}"
                                        data-besar="{{ $s->porsi_besar_default }}"
                                        data-status="{{ $s->status }}"
                                        data-title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button"
                                        class="soft-btn btn-delete btn-delete-trigger"
                                        data-id="{{ $s->id }}"
                                        data-title="Hapus"
                                        data-nama="{{ $s->nama_sekolah }}">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    <form id="delete-form-{{ $s->id }}"
                                        action="{{ route('admin.sekolah.destroy', $s) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Tidak ada data sekolah penerima manfaat
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Modal Edit Sekolah -->
                <div class="modal fade" id="editSekolahModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-semibold">
                                    <i class="bi bi-building me-1"></i>Edit Sekolah Penerima
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-divider"></div>
                            
                            <form id="editForm" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nama Sekolah</label>
                                            <input type="text" name="nama_sekolah"
                                                class="form-control shadow-sm @error('nama_sekolah') is-invalid @enderror"
                                                required>
                                            @error('nama_sekolah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">PIC</label>
                                            <input type="text" name="pic"
                                                class="form-control shadow-sm @error('pic') is-invalid @enderror"
                                                required>
                                            @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Porsi Kecil</label>
                                            <input type="number" name="porsi_kecil_default" min="0"
                                                class="form-control shadow-sm @error('porsi_kecil_default') is-invalid @enderror">
                                            @error('porsi_kecil_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Porsi Besar</label>
                                            <input type="number" name="porsi_besar_default" min="0"
                                                class="form-control shadow-sm @error('porsi_besar_default') is-invalid @enderror">
                                            @error('porsi_besar_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Status</label>
                                            <select name="status"
                                                class="form-select shadow-sm @error('status') is-invalid @enderror">
                                                <option value="Aktif">Aktif</option>
                                                <option value="Nonaktif">Nonaktif</option>
                                            </select>
                                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
            </div>
           <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan <span class="badge bg-primary">{{ $sekolah->firstItem() }}</span>
                    –
                    <span class="badge bg-primary">{{ $sekolah->lastItem() }}</span>
                    dari
                    <span class="badge bg-secondary">{{ $sekolah->total() }}</span>
                    sekolah
                </div>

                {{ $sekolah->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal konfirmasi hapus -->
    <div class="confirm-overlay" id="deleteConfirmOverlay">
        <div class="confirm-card">
            <div class="confirm-icon">
                <i class="bi bi-exclamation-lg"></i>
            </div>
            <h5 class="fw-bold mt-3">Konfirmasi Hapus</h5>
            <p class="text-muted mb-4" id="deleteMessage"></p>

            <div class="d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-secondary px-4" id="cancelDelete">Tidak</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDelete">Ya</button>
            </div>
        </div>
    </div>                        

    <!-- Modal Tambah Sekolah -->
    <div class="modal fade" id="tambahSekolahModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-building me-1"></i>Tambah Sekolah Penerima
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-divider"></div>

                <form action="{{ route('admin.sekolah.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah"
                                    class="form-control shadow-sm @error('nama_sekolah') is-invalid @enderror"
                                    value="{{ old('nama_sekolah') }}" required>
                                @error('nama_sekolah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">PIC</label>
                                <input type="text" name="pic"
                                    class="form-control shadow-sm @error('pic') is-invalid @enderror"
                                    value="{{ old('pic') }}" required>
                                @error('pic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Porsi Kecil <span class="text-muted">(Opsional)</span></label>
                                <input type="number" name="porsi_kecil_default" min="0"
                                    class="form-control shadow-sm @error('porsi_kecil_default') is-invalid @enderror"
                                    value="{{ old('porsi_kecil_default') }}" placeholder="-- Contoh : 2000 --">
                                @error('porsi_kecil_default')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Porsi Besar <span class="text-muted">(Opsional)</span></label>
                                <input type="number" name="porsi_besar_default" min="0"
                                    class="form-control shadow-sm @error('porsi_besar_default') is-invalid @enderror"
                                    value="{{ old('porsi_besar_default') }}" placeholder="-- Contoh : 5000 --">
                                @error('porsi_besar_default')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Status</label>
                                <select name="status"
                                    class="form-select shadow-sm @error('status') is-invalid @enderror" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
</div>
<!-- asc, dsc -->
@php
    function sortLink($label, $column) {
        $currentSort = request('sort_by');
        $currentDir  = request('sort_dir', 'asc');

        $dir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';

        $icon = '';
        if ($currentSort === $column) {
            $icon = $currentDir === 'asc'
                ? '<i class="bi bi-arrow-up ms-1"></i>'
                : '<i class="bi bi-arrow-down ms-1"></i>';
        }

        $url = request()->fullUrlWithQuery([
            'sort_by' => $column,
            'sort_dir' => $dir
        ]);

        return '<a href="'.$url.'" class="text-decoration-none text-dark fw-semibold">'.$label.$icon.'</a>';
    }
@endphp

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

<script>
document.addEventListener('DOMContentLoaded', () => {

    // SUCCESS OVERLAY
    const successOverlay = document.querySelector('.success-overlay');
    if (successOverlay) {
        setTimeout(() => {
            successOverlay.classList.add('show');
        }, 50);

        setTimeout(() => {
            successOverlay.classList.add('hide');
            setTimeout(() => successOverlay.remove(), 300);
        }, 3000);
    }

    // EDIT MODAL
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editForm');

            form.action = `/admin/sekolah/${this.dataset.id}`;
            form.querySelector('[name="nama_sekolah"]').value = this.dataset.nama;
            form.querySelector('[name="pic"]').value = this.dataset.pic;
            form.querySelector('[name="porsi_kecil_default"]').value = this.dataset.kecil;
            form.querySelector('[name="porsi_besar_default"]').value = this.dataset.besar;
            form.querySelector('[name="status"]').value = this.dataset.status;
        });
    });

    // DELETE
    let deleteId = null;
    const overlay = document.getElementById('deleteConfirmOverlay');
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');
    const message = document.getElementById('deleteMessage');

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            deleteId = this.dataset.id;
            message.innerHTML = `Yakin hapus <strong>${this.dataset.nama}</strong>?`;
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