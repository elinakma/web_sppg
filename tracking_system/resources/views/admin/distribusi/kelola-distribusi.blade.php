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
                <a href="{{ route('admin.distribusi.create') }}" class="btn" style="background-color: #133b84; color: white;">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Distribusi
                </a>
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
                                <span class="badge {{ $item->status_color ?? 'bg-secondary' }}">
                                    {{ $item->status_display ?? ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="text-center d-flex justify-content-center flex-wrap gap-1">
                                @if($item->status_display !== 'Selesai')
                                <a href="{{ route('admin.distribusi.total', $item->id) }}"
                                class="btn btn-sm btn-warning action-btn">
                                    <i class="bi bi-truck"></i> Tindak Lanjut
                                </a>
                                @endif

                                <a href="{{ route('admin.distribusi.detail', $item->id) }}" class="btn btn-sm btn-primary me-1">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                <a href="{{ route('admin.distribusi.berita-acara', $item->id) }}" 
                                class="btn btn-sm action-btn" style="background-color: #133b84; color: white;" target="_blank">
                                    <i class="bi bi-printer"></i> Cetak
                                </a>

                                @if($item->status_display !== 'Selesai')
                                <form action="{{ route('admin.distribusi.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus distribusi ini beserta semua datanya?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
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
@endsection
