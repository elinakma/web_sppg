@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

<style>
    .role-admin {
        background: #0d6efd;
        color: #fff;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 13px;
    }

    .role-akuntan {
        background: #ff0000;
        color: #fff;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 13px;
    }

    .role-aslap {
        background: #1fb52e;
        color: #fff;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 13px;
    }

    .role-gizi {
        background: #fe49d7;
        color: #fff;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 13px;
    }

    .role-driver {
        background: #fe9149;
        color: #fff;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 13px;
    }

</style>

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Kelola Pengguna</h4>
                <a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pengguna
                </a>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $key => $user)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->telepon ?? '-' }}</td>
                            <td style="text-align: center;">
                                <span class="role-{{ strtolower($user->role) }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.pengguna.edit', $user) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('admin.pengguna.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada data pengguna
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