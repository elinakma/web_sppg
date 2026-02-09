@extends('layouts.admin')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Tambah Pengguna</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pengguna.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">+62</span>
                        <input 
                            type="text" 
                            name="telepon" 
                            class="form-control @error('telepon') is-invalid @enderror" 
                            placeholder="859xxxxxxxx" 
                            value="{{ old('telepon') }}" 
                            pattern="[8-9][0-9]{8,12}" 
                            title="Nomor dimulai dengan 8 atau 9, tanpa 0 di depan, 9-13 digit"
                        >
                    </div>
                    <small class="form-text text-muted">Contoh: 85940123456 (tanpa 0 di awal)</small>
                    @error('telepon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">Pilih Role</option>
                        <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Aslap" {{ old('role') == 'Aslap' ? 'selected' : '' }}>Asisten Lapangan</option>
                        <option value="Gizi" {{ old('role') == 'Gizi' ? 'selected' : '' }}>Ahli Gizi</option>
                        <option value="Akuntan" {{ old('role') == 'Akuntan' ? 'selected' : '' }}>Akuntansi</option>
                        <option value="Driver" {{ old('role') == 'Driver' ? 'selected' : '' }}>Distribusi</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>                

                <button type="submit" class="btn btn-success">Simpan Pengguna</button>
                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection