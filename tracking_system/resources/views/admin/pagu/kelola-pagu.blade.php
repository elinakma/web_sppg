@extends('layouts.admin')

@section('title', 'Kelola Pagu Harian')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="text-decoration-none fw-semibold" style="color:#133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page">
                        Kelola Pagu Harian
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <!-- Heading -->
            <div class="card mb-4 border-0 bg-success bg-opacity-10">
                <div class="card-body py-3">
                    <h6 class="mb-0 text-success">
                        <i class="bi bi-cash-stack me-2"></i> Kelola Pagu Harian (Global)
                    </h6>
                </div>
            </div>

            @if($pagu)
            <form action="{{ route('admin.pagu.update', $pagu->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Pagu per Porsi Kecil (Rp)</label>
                        <input type="number" id="kecil" name="pagu_porsi_kecil" class="form-control shadow-sm @error('pagu_porsi_kecil') is-invalid @enderror" value="{{ old('pagu_porsi_kecil', $pagu->pagu_porsi_kecil) }}" min="0" readonly>
                        @error('pagu_porsi_kecil') 
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Pagu per Porsi Besar (Rp)</label>
                        <input type="number" id="besar" name="pagu_porsi_besar" class="form-control shadow-sm @error('pagu_porsi_besar') is-invalid @enderror" value="{{ old('pagu_porsi_besar', $pagu->pagu_porsi_besar) }}" min="0" readonly>
                        @error('pagu_porsi_besar') 
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-warning shadow-sm" id="btnEdit"> <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        <button type="submit" id="btnSimpan" disabled class="btn btn-primary px-4 shadow-sm" style="background: linear-gradient(135deg,#1e3a8a,#2563eb); border:none;">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
            @else
            <div class="alert alert-warning border-0 shadow-sm rounded-3 mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Data pagu belum ditambahkan.
            </div>
            @endif
        </div>
    </div>
</div>

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
document.addEventListener('DOMContentLoaded', function(){

    const btnEdit = document.getElementById('btnEdit');
    const btnSimpan = document.getElementById('btnSimpan');
    const kecil = document.getElementById('kecil');
    const besar = document.getElementById('besar');

    btnEdit.addEventListener('click', function(){

        kecil.removeAttribute('readonly');
        besar.removeAttribute('readonly');

        btnSimpan.removeAttribute('disabled');

        kecil.focus();

        btnEdit.innerHTML = '<i class="bi bi-pencil-fill me-1"></i> Edit';
        btnEdit.disabled = true;
    });

});
</script>
@endsection