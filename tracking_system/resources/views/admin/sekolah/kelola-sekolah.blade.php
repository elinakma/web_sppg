@extends('layouts.admin')

@section('title', 'Kelola Sekolah Penerima MBG')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">Kelola Sekolah Penerima MBG</h4>
                <a href="{{ route('admin.sekolah.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Sekolah
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Sekolah</th>
                            <th>PIC</th>
                            <th>Porsi Kecil</th>
                            <th>Porsi Besar</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sekolah as $key => $s)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ $s->nama_sekolah }}</td>
                            <td>{{ $s->pic }}</td>
                            <td class="text-center">{{ $s->porsi_kecil_default }}</td>
                            <td class="text-center">{{ $s->porsi_besar_default }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $s->status === 'Aktif' ? 'success' : 'danger' }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <form action="{{ route('admin.sekolah.destroy', $s) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus sekolah ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit per sekolah -->
                        <div class="modal fade" id="editModal{{ $s->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $s->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel{{ $s->id }}">Edit Sekolah: {{ $s->nama_sekolah }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('admin.sekolah.update', $s) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Nama Sekolah</label>
                                                    <input type="text" name="nama_sekolah" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                                                           value="{{ old('nama_sekolah', $s->nama_sekolah) }}" required>
                                                    @error('nama_sekolah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">PIC</label>
                                                    <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror" 
                                                           value="{{ old('pic', $s->pic) }}" required>
                                                    @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Porsi Kecil</label>
                                                    <input type="number" name="porsi_kecil_default" min="0" class="form-control @error('porsi_kecil_default') is-invalid @enderror" 
                                                           value="{{ old('porsi_kecil_default', $s->porsi_kecil_default) }}" required>
                                                    @error('porsi_kecil_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Porsi Besar</label>
                                                    <input type="number" name="porsi_besar_default" min="0" class="form-control @error('porsi_besar_default') is-invalid @enderror" 
                                                           value="{{ old('porsi_besar_default', $s->porsi_besar_default) }}" required>
                                                    @error('porsi_besar_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                                        <option value="Aktif" {{ old('status', $s->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                        <option value="Nonaktif" {{ old('status', $s->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                    </select>
                                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Tidak ada data sekolah penerima MBG
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