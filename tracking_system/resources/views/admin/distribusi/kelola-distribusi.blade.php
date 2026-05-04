@extends('layouts.admin')

@section('title', 'Kelola Distribusi MBG')

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
                        Kelola Distribusi
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Kelola Distribusi MBG</h4>
                <button type="button"
                    class="btn"
                    style="background-color: #133b84; color: white;"
                    data-bs-toggle="modal"
                    data-bs-target="#tambahDistribusiModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Distribusi
                </button>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Tanggal Awal</th>
                            <th>Tanggal Akhir</th>
                            <th>Status</th>
                            <th width="400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($distribusi as $key => $item)
                        <tr>
                            <td class="text-center">
                                {{ $distribusi->firstItem() + $key }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->tanggal_awal)->format('d M Y') }} 
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->tanggal_akhir)->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                <span class="badge px-3 py-2 text-center d-inline-block {{ $item->status_color ?? 'bg-secondary' }}" style="min-width: 80px;">
                                    {{ $item->status_display ?? ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="text-center d-flex justify-content-center flex-wrap gap-2">
                                @if($item->status_display !== 'Selesai')
                                <a href="{{ route('admin.distribusi.total', $item->id) }}"
                                class="btn btn-sm btn-warning action-btn btn-uniform">
                                    <i class="bi bi-truck me-1"></i> Lanjut
                                </a>
                                @endif

                                <a href="{{ route('admin.distribusi.detail', $item->id) }}" class="btn btn-sm btn-primary btn-uniform">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>

                                <a href="{{ route('admin.distribusi.berita-acara', $item->id) }}" 
                                class="btn btn-sm btn-uniform" style="background-color: #133b84; color: white;" target="_blank">
                                    <i class="bi bi-printer me-1"></i> Cetak
                                </a>

                                @if($item->status_display !== 'Selesai')
                                <button type="button"
                                    class="btn btn-sm btn-danger btn-delete btn-uniform"
                                    data-id="{{ $item->id }}"
                                    data-nama="Distribusi {{ \Carbon\Carbon::parse($item->tanggal_awal)->format('d M Y') }}">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>

                                <form id="delete-form-{{ $item->id }}"
                                    action="{{ route('admin.distribusi.destroy', $item->id) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Belum ada data distribusi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan
                    <strong>{{ $distribusi->firstItem() }}</strong>
                    –
                    <strong>{{ $distribusi->lastItem() }}</strong>
                    dari
                    <strong>{{ $distribusi->total() }}</strong>
                    distribusi
                </div>

                {{ $distribusi->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Distribusi -->
<div class="modal fade" id="tambahDistribusiModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Tambah Distribusi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.distribusi.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control" required>
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
        <p class="text-muted mb-4" id="deleteMessage"></p>

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
    const successOverlay = document.querySelector('.success-overlay');
    if (successOverlay) {
        setTimeout(() => successOverlay.classList.add('show'), 50);

        setTimeout(() => {
            successOverlay.classList.add('hide');
            setTimeout(() => successOverlay.remove(), 300);
        }, 3000);
    }

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
