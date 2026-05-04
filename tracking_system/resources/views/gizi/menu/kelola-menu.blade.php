@extends('layouts.admin')
@section('title', 'Kelola Menu Makanan')

@section('content')
<div class="container py-4">    
    <div class="card shadow-sm">
        <div class="card-body">

            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('gizi.dashboard') }}" class="text-decoration-none fw-semibold" style="color:#133b84">
                            <i class="bi bi-house me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Kelola Menu Makanan</li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Kelola Menu Makanan</h4>
                <button class="btn" style="background-color:#133b84;color:white"
                        data-bs-toggle="modal" data-bs-target="#tambahMenuModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Menu
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- LIST PER HARI --}}
            <div class="accordion" id="accordionMenu">

            @forelse($hariDistribusi as $loopIndex => $hari)
            @php
                $tgl       = $hari->tanggal_harian;
                $tglFmt    = \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM Y');
                $menuHari  = $menuPerTanggal[$tgl] ?? collect();
                $menuKecil = $menuHari->get('kecil', collect());
                $menuBesar = $menuHari->get('besar', collect());
                $totalMenu = $menuKecil->count() + $menuBesar->count();
            @endphp

                <div class="accordion-item mb-3 shadow-sm border">

                    {{-- HEADER --}}
                    <h2 class="accordion-header" id="headingHari{{ $loopIndex }}">
                        <button class="accordion-button {{ $loopIndex !== 0 ? 'collapsed' : '' }} fw-bold bg-light"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseHari{{ $loopIndex }}"
                                aria-expanded="{{ $loopIndex === 0 ? 'true' : 'false' }}"
                                aria-controls="collapseHari{{ $loopIndex }}">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 w-100 me-3">
                                <span>{{ $tglFmt }}</span>
                                <div class="d-flex gap-2 ms-md-auto">
                                    <span class="badge bg-info text-dark">Kecil: {{ $hari->total_porsi_kecil }}</span>
                                    <span class="badge bg-warning text-dark">Besar: {{ $hari->total_porsi_besar }}</span>
                                    <span class="badge bg-secondary">{{ $totalMenu }} menu</span>
                                </div>
                            </div>
                        </button>
                    </h2>

                    {{-- BODY --}}
                    <div id="collapseHari{{ $loopIndex }}"
                        class="accordion-collapse collapse {{ $loopIndex === 0 ? 'show' : '' }}"
                        aria-labelledby="headingHari{{ $loopIndex }}"
                        data-bs-parent="#accordionMenu">
                        <div class="accordion-body">
                            <div class="row g-4">

                                {{-- PORSI KECIL --}}
                                <div class="col-md-6">
                                    <h6 class="mb-3">
                                        <span class="badge bg-info text-dark me-1">Porsi Kecil</span>
                                        {{ $hari->total_porsi_kecil }} porsi
                                    </h6>

                                    @forelse($menuKecil as $menu)
                                    <div class="border rounded-3 p-3 mb-3 bg-white">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong>{{ $menu->nama_menu }}</strong>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editMenuModal{{ $menu->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('gizi.menu.destroy', $menu) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Hapus menu {{ $menu->nama_menu }}?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr><th>Bahan</th><th width="80">Jumlah</th><th width="80">Satuan</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($menu->bahan as $bahan)
                                                <tr>
                                                    <td>{{ $bahan->nama_bahan }}</td>
                                                    <td class="text-center">{{ $bahan->jumlah }}</td>
                                                    <td class="text-center">{{ $bahan->satuan }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @empty
                                    <p class="text-muted small fst-italic">Belum ada menu porsi kecil.</p>
                                    @endforelse
                                </div>

                                {{-- PORSI BESAR --}}
                                <div class="col-md-6">
                                    <h6 class="mb-3">
                                        <span class="badge bg-warning text-dark me-1">Porsi Besar</span>
                                        {{ $hari->total_porsi_besar }} porsi
                                    </h6>

                                    @forelse($menuBesar as $menu)
                                    <div class="border rounded-3 p-3 mb-3 bg-white">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong>{{ $menu->nama_menu }}</strong>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editMenuModal{{ $menu->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('gizi.menu.destroy', $menu) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Hapus menu {{ $menu->nama_menu }}?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr><th>Bahan</th><th width="80">Jumlah</th><th width="80">Satuan</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($menu->bahan as $bahan)
                                                <tr>
                                                    <td>{{ $bahan->nama_bahan }}</td>
                                                    <td class="text-center">{{ $bahan->jumlah }}</td>
                                                    <td class="text-center">{{ $bahan->satuan }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @empty
                                    <p class="text-muted small fst-italic">Belum ada menu porsi besar.</p>
                                    @endforelse
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                {{-- MODAL EDIT — satu per menu, di dalam loop hari --}}
                @foreach($menuKecil as $menu)
                <div class="modal fade" id="editMenuModal{{ $menu->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-semibold">Edit Menu: {{ $menu->nama_menu }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('gizi.menu.update', $menu) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Tanggal</label>
                                            <input type="text" class="form-control bg-light"
                                                value="{{ \Carbon\Carbon::parse($menu->tanggal_menu)->isoFormat('D MMM Y') }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Jenis Porsi</label>
                                            <input type="text" class="form-control bg-light" value="Porsi Kecil" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nama Menu</label>
                                            <input type="text" name="nama_menu" class="form-control"
                                                value="{{ $menu->nama_menu }}" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-semibold mb-0">Daftar Bahan</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="tambahBahanEdit('editBahan{{ $menu->id }}', 'editIdx{{ $menu->id }}')">
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
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

                @foreach($menuBesar as $menu)
                <div class="modal fade" id="editMenuModal{{ $menu->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-semibold">Edit Menu: {{ $menu->nama_menu }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('gizi.menu.update', $menu) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Tanggal</label>
                                            <input type="text" class="form-control bg-light"
                                                value="{{ \Carbon\Carbon::parse($menu->tanggal_menu)->isoFormat('D MMM Y') }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Jenis Porsi</label>
                                            <input type="text" class="form-control bg-light" value="Porsi Besar" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nama Menu</label>
                                            <input type="text" name="nama_menu" class="form-control"
                                                value="{{ $menu->nama_menu }}" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-semibold mb-0">Daftar Bahan</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="tambahBahanEdit('editBahan{{ $menu->id }}', 'editIdx{{ $menu->id }}')">
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
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

                @empty
                <div class="alert alert-info">Belum ada data distribusi yang tersedia.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH MENU --}}
<div class="modal fade" id="tambahMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Tambah Menu Makanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gizi.menu.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <select name="tanggal_menu" class="form-select" required>
                                <option value="">Pilih Tanggal</option>
                                @foreach($hariDistribusi as $h)
                                    <option value="{{ $h->tanggal_harian }}">
                                        {{ \Carbon\Carbon::parse($h->tanggal_harian)->isoFormat('D MMM Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Porsi</label>
                            <select name="jenis_porsi" class="form-select" required>
                                <option value="">Pilih Porsi</option>
                                <option value="kecil">Porsi Kecil</option>
                                <option value="besar">Porsi Besar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Menu</label>
                            <input type="text" name="nama_menu" class="form-control"
                                   placeholder="cth: Ayam Goreng" required>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Daftar Bahan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="tambahBahanBtn">
                            <i class="bi bi-plus"></i> Tambah Bahan
                        </button>
                    </div>
                    <div id="bahanContainer">
                        <div class="row g-2 mb-2 bahan-row">
                            <div class="col-md-5">
                                <input type="text" name="bahan[0][nama_bahan]" class="form-control"
                                       placeholder="Nama bahan" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="bahan[0][jumlah]" class="form-control"
                                       placeholder="Jumlah" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bahan[0][satuan]" class="form-control"
                                       placeholder="kg / gram / buah" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-danger hapus-bahan" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" style="background-color:#133b84;color:white">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ===== TAMBAH (modal tambah) =====
let bahanIndex = 1;

document.getElementById('tambahBahanBtn').addEventListener('click', function () {
    const container = document.getElementById('bahanContainer');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 bahan-row';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="bahan[${bahanIndex}][nama_bahan]" class="form-control" placeholder="Nama bahan" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="bahan[${bahanIndex}][jumlah]" class="form-control" placeholder="Jumlah" step="0.01" min="0" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="bahan[${bahanIndex}][satuan]" class="form-control" placeholder="kg / gram / buah" required>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-danger hapus-bahan">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    bahanIndex++;
    updateHapusBahan();
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.hapus-bahan')) {
        e.target.closest('.bahan-row').remove();
        updateHapusBahan();
    }
});

function updateHapusBahan() {
    const rows = document.querySelectorAll('#bahanContainer .bahan-row');
    rows.forEach(row => {
        row.querySelector('.hapus-bahan').disabled = rows.length === 1;
    });
}

// ===== EDIT (modal edit) =====
// Index counter per modal — disimpan di object berdasarkan containerId
const editIdxMap = {};

function tambahBahanEdit(containerId, idxKey) {
    if (!editIdxMap[idxKey]) {
        // Hitung jumlah bahan yang sudah ada sebagai starting index
        editIdxMap[idxKey] = document.getElementById(containerId).querySelectorAll('.bahan-row-edit').length;
    }
    const idx = editIdxMap[idxKey];
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 bahan-row-edit';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="bahan[${idx}][nama_bahan]" class="form-control" placeholder="Nama bahan" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="bahan[${idx}][jumlah]" class="form-control" placeholder="Jumlah" step="0.01" min="0" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="bahan[${idx}][satuan]" class="form-control" placeholder="kg / gram / buah" required>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="hapusBahanEdit(this, '${containerId}')">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    editIdxMap[idxKey]++;
    updateHapusBahanEdit(containerId);
}

function hapusBahanEdit(btn, containerId) {
    const rows = document.getElementById(containerId).querySelectorAll('.bahan-row-edit');
    if (rows.length <= 1) return; // jangan hapus jika tinggal 1
    btn.closest('.bahan-row-edit').remove();
    updateHapusBahanEdit(containerId);
}

function updateHapusBahanEdit(containerId) {
    const rows = document.getElementById(containerId).querySelectorAll('.bahan-row-edit');
    rows.forEach(row => {
        const btn = row.querySelector('button.btn-danger');
        if (btn) btn.disabled = rows.length === 1;
    });
}
</script>
@endsection