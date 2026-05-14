{{-- resources/views/gizi/menu/_modal-edit.blade.php --}}
<div class="modal fade" id="editMenuModal{{ $menu->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Menu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('gizi.menu.update', $menu) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body pt-2">

                    {{-- Info (readonly) --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ \Carbon\Carbon::parse($menu->tanggal_menu)->locale('id')->isoFormat('dddd, D MMM Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Porsi <span class="text-danger">*</span></label>
                            <select name="jenis_porsi" class="form-select" required>
                                <option value="kecil" {{ $menu->jenis_porsi === 'kecil' ? 'selected' : '' }}>Porsi Kecil</option>
                                <option value="besar" {{ $menu->jenis_porsi === 'besar' ? 'selected' : '' }}>Porsi Besar</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="nama_menu" class="form-control"
                               value="{{ $menu->nama_menu }}" required>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Daftar Bahan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="tambahBahanEdit('editBahan{{ $menu->id }}', 'idx{{ $menu->id }}')">
                            <i class="bi bi-plus"></i> Tambah Bahan
                        </button>
                    </div>

                    <div id="editBahan{{ $menu->id }}">
                        @foreach($menu->bahan as $i => $bahan)
                        <div class="row g-2 mb-2 bahan-row-edit">
                            <div class="col-md-5">
                                <input type="text" name="bahan[{{ $i }}][nama_bahan]"
                                       class="form-control" value="{{ $bahan->nama_bahan }}" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="bahan[{{ $i }}][jumlah]"
                                       class="form-control" value="{{ $bahan->jumlah }}"
                                       step="0.01" min="0" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bahan[{{ $i }}][satuan]"
                                       class="form-control" value="{{ $bahan->satuan }}" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="hapusBahanEdit(this, 'editBahan{{ $menu->id }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>