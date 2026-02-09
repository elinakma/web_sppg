@extends('layouts.admin')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Edit Pengguna - {{ $user->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pengguna.update', $user) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Password (kosongkan jika tidak ingin ubah)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">+62</span>
                        <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" 
                               value="{{ old('telepon', $user->telepon) }}" 
                               placeholder="859xxxxxxxx" 
                               pattern="[8-9][0-9]{8,12}">
                    </div>
                    @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="Admin"  {{ old('role', $user->role) == 'Admin'  ? 'selected' : '' }}>Admin</option>
                        <option value="Aslap"  {{ old('role', $user->role) == 'Aslap'  ? 'selected' : '' }}>Asisten Lapangan</option>
                        <option value="Gizi"   {{ old('role', $user->role) == 'Gizi'   ? 'selected' : '' }}>Ahli Gizi</option>
                        <option value="Akuntan"{{ old('role', $user->role) == 'Akuntan'? 'selected' : '' }}>Akuntansi</option>
                        <option value="Driver" {{ old('role', $user->role) == 'Driver' ? 'selected' : '' }}>Distribusi</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection