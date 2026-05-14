@extends('layouts.admin')

@section('title', 'Kelola Template Menu')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('gizi.dashboard') }}" class="text-decoration-none fw-semibold" style="color:#133b84">
                            <i class="bi bi-house me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Kelola Template</li>
                </ol>
            </nav>
            <hr class="mt-0 mb-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-0">Template Menu Makanan</h4>
                    <p class="text-muted small mb-0">Template dapat dipilih saat menambah menu harian.</p>
                </div>
                <button class="btn btn-sm text-white rounded-pill px-3 shadow-sm" 
                        style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;"
                        data-bs-toggle="modal" data-bs-target="#tambahTemplateModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Template
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
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ── Porsi Kecil ── --}}
            <h5 class="fw-semibold mt-2 mb-3">
                <span class="badge status-badge rounded-pill bg-info text-dark">Porsi Kecil</span>
            </h5>

            @if(($templates->get('kecil') ?? collect())->isEmpty())
                <p class="text-muted fst-italic small mb-4">Belum ada template porsi kecil.</p>
            @else
            <div class="row g-3 mb-4">
                @foreach($templates->get('kecil') as $tpl)
                <div class="col-md-6 col-lg-4">
                    <div class="card border h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">{{ $tpl->nama_menu }}</h6>
                                <div class="d-flex gap-1">
                                    <button class="soft-btn btn-next btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTplModal{{ $tpl->id }}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('gizi.template.destroy', $tpl) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="soft-btn btn-delete" title="Hapus"
                                                onclick="return confirm('Hapus template {{ addslashes($tpl->nama_menu) }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                                <thead class="table-light">
                                    <tr><th>Bahan</th><th class="text-center">Jml</th><th class="text-center">Satuan</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($tpl->bahan as $b)
                                    <tr>
                                        <td>{{ $b->nama_bahan }}</td>
                                        <td class="text-center fw-medium">
                                            @php
                                                $jumlahFmt = is_numeric($b->jumlah) 
                                                    ? rtrim(rtrim(number_format($b->jumlah, 2, ',', '.'), '0'), ',') 
                                                    : $b->jumlah;
                                            @endphp
                                            {{ $jumlahFmt }}
                                        </td>
                                        <td class="text-center">{{ $b->satuan }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ── Porsi Besar ── --}}
            <h5 class="fw-semibold mb-3">
                <span class="badge status-badge rounded-pill bg-warning text-dark">Porsi Besar</span>
            </h5>

            @if(($templates->get('besar') ?? collect())->isEmpty())
                <p class="text-muted fst-italic small">Belum ada template porsi besar.</p>
            @else
            <div class="row g-3">
                @foreach($templates->get('besar') as $tpl)
                <div class="col-md-6 col-lg-4">
                    <div class="card border h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">{{ $tpl->nama_menu }}</h6>
                                <div class="d-flex gap-1">
                                    <button class="soft-btn btn-next btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTplModal{{ $tpl->id }}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('gizi.template.destroy', $tpl) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="soft-btn btn-delete" title="Hapus"
                                                onclick="return confirm('Hapus template {{ addslashes($tpl->nama_menu) }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                                <thead class="table-light">
                                    <tr><th>Bahan</th><th class="text-center">Jml</th><th class="text-center">Satuan</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($tpl->bahan as $b)
                                    <tr>
                                        <td>{{ $b->nama_bahan }}</td>
                                        <td class="text-center fw-medium">
                                            @php
                                                $jumlahFmt = is_numeric($b->jumlah) 
                                                    ? rtrim(rtrim(number_format($b->jumlah, 2, ',', '.'), '0'), ',') 
                                                    : $b->jumlah;
                                            @endphp
                                            {{ $jumlahFmt }}
                                        </td>
                                        <td class="text-center">{{ $b->satuan }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal Edit untuk setiap template --}}
@foreach(($templates->get('kecil') ?? collect())->merge($templates->get('besar') ?? collect()) as $tpl)
<div class="modal fade" id="editTplModal{{ $tpl->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Template: {{ $tpl->nama_menu }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gizi.template.update', $tpl) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body pt-2">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama_menu" class="form-control"
                                   value="{{ $tpl->nama_menu }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Porsi <span class="text-danger">*</span></label>
                            <select name="jenis_porsi" class="form-select" required>
                                <option value="kecil" {{ $tpl->jenis_porsi === 'kecil' ? 'selected' : '' }}>Porsi Kecil</option>
                                <option value="besar" {{ $tpl->jenis_porsi === 'besar' ? 'selected' : '' }}>Porsi Besar</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Daftar Bahan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="tambahBahanEdit('tplBahan{{ $tpl->id }}', 'tplIdx{{ $tpl->id }}')">
                            <i class="bi bi-plus"></i> Tambah Bahan
                        </button>
                    </div>
                    <div id="tplBahan{{ $tpl->id }}">
                        @foreach($tpl->bahan as $i => $b)
                        <div class="row g-2 mb-2 bahan-row-edit">
                            <div class="col-md-5">
                                <input type="text" name="bahan[{{ $i }}][nama_bahan]"
                                       class="form-control" value="{{ $b->nama_bahan }}" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="bahan[{{ $i }}][jumlah]"
                                       class="form-control" value="{{ $b->jumlah }}"
                                       step="0.01" min="0" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bahan[{{ $i }}][satuan]"
                                       class="form-control" value="{{ $b->satuan }}" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="hapusBahanEdit(this, 'tplBahan{{ $tpl->id }}')">
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
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="tambahTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-journals text-primary me-2"></i>Tambah Template Menu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gizi.template.store') }}" method="POST" id="formTambahTemplate">
                @csrf
                <div class="modal-body pt-2">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama_menu" class="form-control"
                                   placeholder="cth: Ayam Goreng Bumbu Kuning" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Porsi <span class="text-danger">*</span></label>
                            <select name="jenis_porsi" class="form-select" required>
                                <option value="">— Pilih —</option>
                                <option value="kecil">Porsi Kecil</option>
                                <option value="besar">Porsi Besar</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Daftar Bahan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="tambahBahanTplBtn">
                            <i class="bi bi-plus"></i> Tambah Bahan
                        </button>
                    </div>
                    <div id="bahanTplContainer">
                        <div class="row g-2 mb-2 bahan-tpl-row">
                            <div class="col-md-5">
                                <input type="text" name="bahan[0][nama_bahan]"
                                       class="form-control" placeholder="Nama bahan" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="bahan[0][jumlah]"
                                       class="form-control" placeholder="Jumlah" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bahan[0][satuan]"
                                       class="form-control" placeholder="kg / gram / buah" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-danger hapus-bahan-tpl" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background:#133b84">
                        <i class="bi bi-save me-1"></i> Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── Tambah Bahan di modal Tambah Template ──────────────────────────────
let tplBahanIdx = 1;

document.getElementById('tambahBahanTplBtn').addEventListener('click', () => {
    const container = document.getElementById('bahanTplContainer');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 bahan-tpl-row';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="bahan[${tplBahanIdx}][nama_bahan]"
                   class="form-control" placeholder="Nama bahan" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="bahan[${tplBahanIdx}][jumlah]"
                   class="form-control" placeholder="Jumlah" step="0.01" min="0" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="bahan[${tplBahanIdx}][satuan]"
                   class="form-control" placeholder="kg / gram / buah" required>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-danger hapus-bahan-tpl">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    container.appendChild(row);
    tplBahanIdx++;
    updateHapusTplBtn();
});

document.addEventListener('click', e => {
    if (e.target.closest('#bahanTplContainer .hapus-bahan-tpl')) {
        e.target.closest('.bahan-tpl-row').remove();
        updateHapusTplBtn();
    }
});

function updateHapusTplBtn() {
    const rows = document.querySelectorAll('#bahanTplContainer .bahan-tpl-row');
    rows.forEach(r => r.querySelector('.hapus-bahan-tpl').disabled = rows.length === 1);
}

// ── Tambah / Hapus Bahan di modal Edit Template ────────────────────────
const editIdxMap = {};

function tambahBahanEdit(containerId, idxKey) {
    if (editIdxMap[idxKey] === undefined) {
        editIdxMap[idxKey] = document.getElementById(containerId)
            .querySelectorAll('.bahan-row-edit').length;
    }
    const idx = editIdxMap[idxKey];
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 bahan-row-edit';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="bahan[${idx}][nama_bahan]"
                   class="form-control" placeholder="Nama bahan" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="bahan[${idx}][jumlah]"
                   class="form-control" placeholder="Jumlah" step="0.01" min="0" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="bahan[${idx}][satuan]"
                   class="form-control" placeholder="kg / gram / buah" required>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-danger"
                    onclick="hapusBahanEdit(this, '${containerId}')">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    container.appendChild(row);
    editIdxMap[idxKey]++;
    updateHapusBahanEdit(containerId);
}

function hapusBahanEdit(btn, containerId) {
    const rows = document.getElementById(containerId).querySelectorAll('.bahan-row-edit');
    if (rows.length <= 1) return;
    btn.closest('.bahan-row-edit').remove();
    updateHapusBahanEdit(containerId);
}

function updateHapusBahanEdit(containerId) {
    const rows = document.getElementById(containerId).querySelectorAll('.bahan-row-edit');
    rows.forEach(r => {
        const btn = r.querySelector('button.btn-danger');
        if (btn) btn.disabled = rows.length === 1;
    });
}
</script>
@endsection