@extends('layouts.admin')

@section('title', 'Tambah Sekolah Baru')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Tambah Sekolah Penerima MBG</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sekolah.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" class="form-control @error('nama_sekolah') is-invalid @enderror"
                           value="{{ old('nama_sekolah') }}" required>
                    @error('nama_sekolah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">PIC (Penanggung Jawab)</label>
                    <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror"
                           value="{{ old('pic') }}" required>
                    @error('pic')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="">Pilih Status</option>
                        <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('admin.sekolah.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection