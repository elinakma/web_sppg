@extends('layouts.admin')

@section('title', 'Kelola Distribusi MBG')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Kelola Distribusi MBG</h4>
                <a href="{{ route('distribusi.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Distribusi
                </a>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Tanggal Distribusi</th>
                            <th>Status</th>
                            <th width="400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($distribusi as $key => $distribusi)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($distribusi->tanggal_distribusi)->format('d M Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">
                                    {{ ucfirst($distribusi->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('distribusi.total', $distribusi->id) }}"
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-truck"></i> Tindak Lanjut
                                </a>
                                <a href="#"
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-truck"></i> Detail
                                </a>
                                <a href="{{ route('distribusi.berita-acara', $distribusi->id) }}" 
                                class="btn btn-sm" style="background-color: #133b84; color: white;" target="_blank">
                                    <i class="bi bi-printer"></i> Cetak Berita Acara
                                </a>
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

        </div>
    </div>
</div>
@endsection
