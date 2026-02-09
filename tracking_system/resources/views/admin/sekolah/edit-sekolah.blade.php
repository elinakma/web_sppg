@extends('layouts.admin')

@section('title', 'Edit Sekolah')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Edit Sekolah - {{ $sekolah->nama_sekolah }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sekolah.update', $sekolah) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" class="form-control @error('nama_sekolah') is-invalid @enderror"
                           value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}" required>
                    @error('nama_sekolah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">PIC (Penanggung Jawab)</label>
                    <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror"
                           value="{{ old('pic', $sekolah->pic) }}" required>
                    @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="Aktif" {{ old('status', $sekolah->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Nonaktif" {{ old('status', $sekolah->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="{{ route('admin.sekolah.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection