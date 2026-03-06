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
                        Kelola Sekolah Penerima MBG
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <h4 class="mb-0 fw-bold">Kelola Sekolah Penerima MBG</h4>

                <div class="d-flex gap-2">
                    <!-- Search -->
                    <form action="{{ route('admin.sekolah.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text"
                                name="search"
                                class="form-control"
                                placeholder="Cari sekolah / PIC..."
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
                        data-bs-target="#tambahSekolahModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Sekolah
                    </button>

                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>{!! sortLink('Nama Sekolah', 'nama_sekolah') !!}</th>
                            <th>{!! sortLink('PIC', 'pic') !!}</th>
                            <th>{!! sortLink('Porsi Kecil', 'porsi_kecil_default') !!}</th>
                            <th>{!! sortLink('Porsi Besar', 'porsi_besar_default') !!}</th>
                            <th>{!! sortLink('Status', 'status') !!}</th>
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
                                <span class="badge bg-{{ $s->status === 'Aktif' ? 'success' : 'danger' }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="text-center d-flex justify-content-center flex-wrap gap-1">
                                <button type="button" class="btn btn-sm btn-warning action-btn" 
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <form action="{{ route('admin.sekolah.destroy', $s) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger action-btn"
                                            onclick="return confirm('Yakin ingin menghapus sekolah ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit per sekolah -->
                        <div class="modal fade" id="editModal{{ $s->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $s->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel{{ $s->id }}">Edit Sekolah: {{ $s->nama_sekolah }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('admin.sekolah.update', $s) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Nama Sekolah</label>
                                                    <input type="text" name="nama_sekolah" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                                                           value="{{ old('nama_sekolah', $s->nama_sekolah) }}" required>
                                                    @error('nama_sekolah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">PIC</label>
                                                    <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror" 
                                                           value="{{ old('pic', $s->pic) }}" required>
                                                    @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Porsi Kecil</label>
                                                    <input type="number" name="porsi_kecil_default" min="0" class="form-control @error('porsi_kecil_default') is-invalid @enderror" 
                                                           value="{{ old('porsi_kecil_default', $s->porsi_kecil_default) }}" required>
                                                    @error('porsi_kecil_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Porsi Besar</label>
                                                    <input type="number" name="porsi_besar_default" min="0" class="form-control @error('porsi_besar_default') is-invalid @enderror" 
                                                           value="{{ old('porsi_besar_default', $s->porsi_besar_default) }}" required>
                                                    @error('porsi_besar_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                                        <option value="Aktif" {{ old('status', $s->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                        <option value="Nonaktif" {{ old('status', $s->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                    </select>
                                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
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
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Tidak ada data sekolah penerima MBG
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan
                    <strong>{{ $sekolah->firstItem() }}</strong>
                    –
                    <strong>{{ $sekolah->lastItem() }}</strong>
                    dari
                    <strong>{{ $sekolah->total() }}</strong>
                    sekolah
                </div>

                {{ $sekolah->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Sekolah -->
    <div class="modal fade" id="tambahSekolahModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Tambah Sekolah Penerima MBG</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('admin.sekolah.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah"
                                    class="form-control @error('nama_sekolah') is-invalid @enderror"
                                    value="{{ old('nama_sekolah') }}" required>
                                @error('nama_sekolah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">PIC</label>
                                <input type="text" name="pic"
                                    class="form-control @error('pic') is-invalid @enderror"
                                    value="{{ old('pic') }}" required>
                                @error('pic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Porsi Kecil <span class="text-muted">(Opsional)</span>
                                </label>
                                <input type="number" name="porsi_kecil_default" min="0"
                                    class="form-control @error('porsi_kecil_default') is-invalid @enderror"
                                    value="{{ old('porsi_kecil_default') }}">
                                @error('porsi_kecil_default')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Porsi Besar <span class="text-muted">(Opsional)</span>
                                </label>
                                <input type="number" name="porsi_besar_default" min="0"
                                    class="form-control @error('porsi_besar_default') is-invalid @enderror"
                                    value="{{ old('porsi_besar_default') }}">
                                @error('porsi_besar_default')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Status</label>
                                <select name="status"
                                    class="form-select @error('status') is-invalid @enderror" required>
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

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn" style="background-color:#133b84;color:white">
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
        <h5 class="fw-bold mt-3">Success</h5>
        <p class="text-muted mb-0">
            {{ session('success') }}
        </p>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
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

@endsection