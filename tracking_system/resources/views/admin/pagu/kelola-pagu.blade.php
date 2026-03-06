@extends('layouts.admin')

@section('title', 'Kelola Pagu Harian')

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
                        Kelola Pagu Harian
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <h4 class="mb-4 fw-bold">Kelola Pagu Harian (Global)</h4>

            @if($pagu)
            <form action="{{ route('admin.pagu.update', $pagu->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Pagu per Porsi Kecil (Rp)</label>
                        <input type="number" name="pagu_porsi_kecil" class="form-control @error('pagu_porsi_kecil') is-invalid @enderror"
                               value="{{ old('pagu_porsi_kecil', $pagu->pagu_porsi_kecil) }}" min="0" required>
                        @error('pagu_porsi_kecil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pagu per Porsi Besar (Rp)</label>
                        <input type="number" name="pagu_porsi_besar" class="form-control @error('pagu_porsi_besar') is-invalid @enderror"
                               value="{{ old('pagu_porsi_besar', $pagu->pagu_porsi_besar) }}" min="0" required>
                        @error('pagu_porsi_besar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn" style="background-color: #133b84; color: white;">
                            <i class="bi bi-save me-1"></i> Simpan Pagu
                        </button>
                    </div>
                </div>
            </form>
            @else
            <p class="text-danger">Data pagu belum ada. Silakan tambahkan secara manual di database.</p>
            @endif
        </div>
    </div>
</div>
@endsection