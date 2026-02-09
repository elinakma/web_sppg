@extends('layouts.admin')

@section('title', 'Tambah Distribusi MBG')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="fw-bold mb-4">Tambah Distribusi MBG</h4>

            <form action="{{ route('distribusi.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Tanggal Distribusi</label>
                    <input type="date" name="tanggal_distribusi"
                           class="form-control" required>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('distribusi.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection