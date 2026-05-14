@extends('layouts.admin')

@section('title', 'Kelola Menu Makanan')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('gizi.dashboard') }}" class="text-decoration-none fw-semibold" style="color:#133b84">
                            <i class="bi bi-house me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Kelola Menu Makanan</li>
                </ol>
            </nav>
            
            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4 class="fw-bold mb-0">Menu Makanan Harian</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('gizi.template.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-journals me-1"></i> Kelola Template
                    </a>
                    <button class="btn btn-sm text-white rounded-pill px-3 shadow-sm" 
                            style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;"
                            data-bs-toggle="modal" data-bs-target="#tambahMenuModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Menu
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Accordion per hari -->
            <div class="accordion custom-accordion" id="accordionMenu">
                @forelse($hariDistribusi as $loopIndex => $hari)
                @php
                    $tgl       = \Carbon\Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
                    $tglFmt    = \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM Y');
                    $menuHari  = $menuPerTanggal[$tgl] ?? collect();
                    $menuKecil = $menuHari->get('kecil', collect());
                    $menuBesar = $menuHari->get('besar', collect());
                    $totalMenu = $menuKecil->count() + $menuBesar->count();
                @endphp

                <div class="accordion-item mb-3 shadow-sm border-0 rounded-3 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loopIndex !== 0 ? 'collapsed' : '' }} fw-bold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseHari{{ $loopIndex }}"
                                style="background-color: #f8f9fa;">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 w-100 me-3">
                                <span style="color: #133b84;">{{ $tglFmt }}</span>

                                <div class="d-flex gap-2 ms-md-auto align-items-center">
                                    <span class="badge rounded-pill bg-info text-dark"
                                        style="
                                            min-width:80px;
                                            padding:6px 12px;
                                            font-size:12px;
                                            font-weight:500;
                                            border-radius:30px;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.12);
                                            letter-spacing:0.3px;
                                        ">
                                        Kecil: {{ $hari->total_porsi_kecil }}
                                    </span>

                                    <span class="badge rounded-pill bg-warning text-dark"
                                        style="
                                            min-width:80px;
                                            padding:6px 12px;
                                            font-size:12px;
                                            font-weight:500;
                                            border-radius:30px;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.12);
                                            letter-spacing:0.3px;
                                        ">
                                        Besar: {{ $hari->total_porsi_besar }}
                                    </span>

                                    <span
                                        onclick="event.stopPropagation(); openAkgModal('{{ $hari->tanggal_harian }}', '{{ $tglFmt }}')"
                                        title="Hitung AKG"
                                        style="
                                            min-width:34px;
                                            height:34px;
                                            padding:6px 12px;
                                            border-radius:30px;
                                            background:#e9f2ff;
                                            color:#0d6efd;
                                            display:inline-flex;
                                            align-items:center;
                                            justify-content:center;
                                            font-size:14px;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.12);
                                            transition:all .25s ease;
                                        ">
                                        <i class="bi bi-calculator"></i>
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>

                    <div id="collapseHari{{ $loopIndex }}"
                         class="accordion-collapse collapse {{ $loopIndex === 0 ? 'show' : '' }}"
                         data-bs-parent="#accordionMenu">
                        <div class="accordion-body bg-white">
                            {{-- TABEL AKG HARIAN --}}
                            @php 
                                $akg = $akgHarian[$tgl] ?? null; 
                            @endphp

                            @if($akg)
                            <div class="mb-4">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="bi bi-clipboard-data me-2"></i> 
                                    Angka Kecukupan Gizi (AKG) Harian
                                </h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th width="120">Jenis Porsi</th>
                                                <th>Energi (kkal)</th>
                                                <th>Protein (g)</th>
                                                <th>Lemak (g)</th>
                                                <th>Karbo (g)</th>
                                                <th>Serat (g)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold bg-info text-white text-center">Porsi Kecil</td>
                                                <td class="text-center">{{ number_format($akg->energi_kecil ?? 0) }}</td>
                                                <td class="text-center">{{ $akg->protein_kecil ?? 0 }}</td>
                                                <td class="text-center">{{ $akg->lemak_kecil ?? 0 }}</td>
                                                <td class="text-center">{{ $akg->karbo_kecil ?? 0 }}</td>
                                                <td class="text-center">{{ $akg->serat_kecil ?? 0 }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold bg-warning text-dark text-center">Porsi Besar</td>
                                                <td class="text-center">{{ number_format($akg->energi_besar ?? 0) }}</td>
                                                <td class="text-center">{{ $akg->protein_besar ?? 0 }}</td>
                                                <td class="text-center">{{ $akg->lemak_besar ?? 0 }}</td>
                                                <td class="text-center">{{ $akg->karbo_besar ?? 0 }}</td>
                                                <td class="text-center">{{ $akg->serat_besar ?? 0 }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning border-0 py-2 mb-4">
                                <i class="bi bi-info-circle"></i> 
                                AKG harian belum diinput untuk tanggal ini.
                            </div>
                            @endif

                            <div class="row g-4">
                                {{-- Porsi Kecil --}}
                                <div class="col-md-6 border-end-md">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                                        <i class="bi bi-box-seam me-2 text-info"></i> Porsi Kecil
                                    </h6>
                                    @forelse($menuKecil as $menu)
                                        @include('gizi.menu._menu-card', ['menu' => $menu])
                                    @empty
                                        <div class="p-3 border rounded text-center bg-light">
                                            <p class="text-muted small mb-0 fst-italic">Belum ada menu porsi kecil.</p>
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Porsi Besar --}}
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                                        <i class="bi bi-box-seam-fill me-2 text-warning"></i> Porsi Besar
                                    </h6>
                                    @forelse($menuBesar as $menu)
                                        @include('gizi.menu._menu-card', ['menu' => $menu])
                                    @empty
                                        <div class="p-3 border rounded text-center bg-light">
                                            <p class="text-muted small mb-0 fst-italic">Belum ada menu porsi besar.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Edit diletakkan di sini --}}
                @foreach($menuKecil->merge($menuBesar) as $menu)
                    @include('gizi.menu._modal-edit', ['menu' => $menu])
                @endforeach

                @empty
                <div class="alert alert-info border-0 shadow-sm text-center py-4">
                    <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                    Belum ada data distribusi yang tersedia.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH MENU (Sesuai Style Kelola Sekolah) -->
<div class="modal fade" id="tambahMenuModal" tabindex="-1" aria-hidden="true" style="overflow-y: auto;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="max-height: 90vh;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Menu Makanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-divider"></div>

            <form action="{{ route('gizi.menu.store') }}" method="POST" id="formTambahMenu">
                @csrf
                <div class="modal-body">
                    {{-- Form konten tetap sama namun dengan sentuhan shadow-sm --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal <span class="text-danger">*</span></label>
                            <select name="tanggal_menu" class="form-select shadow-sm" required>
                                <option value="">— Pilih Tanggal —</option>
                                @foreach($hariDistribusi as $h)
                                    <option value="{{ \Carbon\Carbon::parse($h->tanggal_harian)->format('Y-m-d') }}">
                                        {{ \Carbon\Carbon::parse($h->tanggal_harian)->locale('id')->isoFormat('dddd, D MMM Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Porsi <span class="text-danger">*</span></label>
                            <select name="jenis_porsi" id="jenisPorsiTambah" class="form-select shadow-sm" required>
                                <option value="">— Pilih Porsi —</option>
                                <option value="kecil">Porsi Kecil</option>
                                <option value="besar">Porsi Besar</option>
                            </select>
                        </div>
                    </div>

                    <div class="card border-0 bg-light rounded-3 p-3 mb-4">
                        <div class="fw-bold mb-2 small text-muted text-uppercase">
                            <i class="bi bi-search me-1"></i> Cari dari Template
                        </div>
                        <input type="text" id="templateSearch" class="form-control form-control-sm rounded-pill mb-2" placeholder="Ketik nama menu...">
                        <div id="templateList" class="bg-white rounded border" style="max-height:150px; overflow-y:auto;">
                            <div class="text-muted small fst-italic text-center py-3" id="templateEmptyMsg">
                                Pilih jenis porsi atau cari template.
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="nama_menu" id="namaMenuInput" class="form-control shadow-sm" placeholder="cth: Ayam Goreng" required>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="simpan_sebagai_template" id="simpanTemplate" value="1">
                        <label class="form-check-label small" for="simpanTemplate">Simpan sebagai Template Menu baru</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 small text-uppercase"><i class="bi bi-egg-fried me-2"></i>Daftar Bahan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="tambahBahanBtn">
                            <i class="bi bi-plus"></i> Tambah
                        </button>
                    </div>
                    <div id="bahanContainer">
                        <div class="row g-2 mb-2 bahan-row">
                            <div class="col-md-5">
                                <input type="text" name="bahan[0][nama_bahan]" class="form-control shadow-sm" placeholder="Nama bahan" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="bahan[0][jumlah]" class="form-control shadow-sm" placeholder="Jumlah" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bahan[0][satuan]" class="form-control  shadow-sm" placeholder="Satuan" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="soft-btn btn-delete"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;">
                        Simpan 
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL AKG HARIAN ==================== -->
<div class="modal fade" id="modalAkgHarian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-calculator me-2 text-success"></i>
                    Input AKG Harian - <span id="akgTanggalTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('gizi.akg.store') }}" method="POST" id="formAkgHarian">
                @csrf
                <input type="hidden" name="tanggal_harian" id="inputTanggalHarian">

                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Porsi Kecil -->
                        <div class="col-lg-6">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info text-white fw-semibold">
                                    <i class="bi bi-box-seam"></i> Porsi Kecil
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label small">Energi (kkal)</label>
                                            <input type="number" name="energi_kecil" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Karbohidrat (g)</label>
                                            <input type="number" step="0.1" name="karbo_kecil" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Lemak (g)</label>
                                            <input type="number" step="0.1" name="lemak_kecil" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Protein (g)</label>
                                            <input type="number" step="0.1" name="protein_kecil" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Serat (g)</label>
                                            <input type="number" step="0.1" name="serat_kecil" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Porsi Besar -->
                        <div class="col-lg-6">
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark fw-semibold">
                                    <i class="bi bi-box-seam-fill"></i> Porsi Besar
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label small">Energi (kkal)</label>
                                            <input type="number" name="energi_besar" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Karbohidrat (g)</label>
                                            <input type="number" step="0.1" name="karbo_besar" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Lemak (g)</label>
                                            <input type="number" step="0.1" name="lemak_besar" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Protein (g)</label>
                                            <input type="number" step="0.1" name="protein_besar" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Serat (g)</label>
                                            <input type="number" step="0.1" name="serat_besar" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Simpan AKG Harian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Data template JS -->
<script id="templateData" type="application/json">
    {!! json_encode($allTemplates) !!}
</script>

@endsection

@section('scripts')
<script>

const allTemplates = JSON.parse(document.getElementById('templateData').textContent);

let bahanIndex = 1;

function buatBahanRow(idx, data = {}) {
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 bahan-row';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="bahan[${idx}][nama_bahan]"
                   class="form-control shadow-sm" placeholder="Nama bahan"
                   value="${data.nama_bahan ?? ''}" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="bahan[${idx}][jumlah]"
                   class="form-control shadow-sm" placeholder="Jumlah" step="0.01" min="0"
                   value="${data.jumlah ?? ''}" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="bahan[${idx}][satuan]"
                   class="form-control shadow-sm" placeholder="kg / gram / buah"
                   value="${data.satuan ?? ''}" required>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="soft-btn btn-delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    return row;
}

function updateHapusBtn() {
    const rows = document.querySelectorAll('#bahanContainer .bahan-row');
    rows.forEach(r => r.querySelector('.hapus-bahan').disabled = rows.length === 1);
}

document.getElementById('tambahBahanBtn').addEventListener('click', () => {
    document.getElementById('bahanContainer').appendChild(buatBahanRow(bahanIndex++));
    updateHapusBtn();
});

document.addEventListener('click', e => {
    if (e.target.closest('#bahanContainer .hapus-bahan')) {
        e.target.closest('.bahan-row').remove();
        updateHapusBtn();
    }
});

const jenisPorsiSel  = document.getElementById('jenisPorsiTambah');
const templateSearch = document.getElementById('templateSearch');
const templateList   = document.getElementById('templateList');
const emptyMsg       = document.getElementById('templateEmptyMsg');
const namaMenuInput  = document.getElementById('namaMenuInput');
const bahanContainer = document.getElementById('bahanContainer');

function renderTemplateList(keyword = '') {
    const jenis  = jenisPorsiSel.value;
    const kw     = keyword.toLowerCase().trim();

    let filtered = allTemplates;
    if (jenis)  filtered = filtered.filter(t => t.jenis_porsi === jenis);
    if (kw)     filtered = filtered.filter(t => t.nama_menu.toLowerCase().includes(kw));

    templateList.innerHTML = '';

    if (filtered.length === 0) {
        templateList.innerHTML = `
            <div class="text-muted small fst-italic text-center py-2">
                ${kw ? 'Tidak ada template yang cocok.' : 'Belum ada template untuk porsi ini.'}
            </div>`;
        return;
    }

    filtered.forEach(tpl => {
        const item = document.createElement('button');
        item.type  = 'button';
        item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-0 rounded mb-1';
        item.style.cssText = 'font-size:.875rem; background:#fff; border:1px solid #e5e7eb !important;';
        item.innerHTML = `
            <span>
                <i class="bi bi-journal-text text-primary me-2"></i>
                <strong>${tpl.nama_menu}</strong>
                <span class="text-muted ms-1">(${tpl.bahan.length} bahan)</span>
            </span>
            <span class="badge status-badge ${tpl.jenis_porsi === 'kecil' ? 'bg-info text-dark' : 'bg-warning text-dark'}">
                ${tpl.jenis_porsi === 'kecil' ? 'Kecil' : 'Besar'}
            </span>`;

        item.addEventListener('click', () => applyTemplate(tpl, item));
        templateList.appendChild(item);
    });
}

function applyTemplate(tpl, clickedEl) {
    // Highlight item terpilih
    document.querySelectorAll('#templateList .list-group-item').forEach(el => {
        el.classList.remove('active');
        el.style.background = '#fff';
        el.style.color = '';
    });
    clickedEl.classList.add('active');
    clickedEl.style.background = '#133b84';
    clickedEl.style.color = '#fff';

    // Auto-fill nama dan jenis porsi
    namaMenuInput.value   = tpl.nama_menu;
    jenisPorsiSel.value   = tpl.jenis_porsi;

    // Reset dan isi bahan
    bahanContainer.innerHTML = '';
    bahanIndex = 0;

    tpl.bahan.forEach(b => {
        bahanContainer.appendChild(buatBahanRow(bahanIndex++, b));
    });

    // Jika template tidak punya bahan, tambahkan satu baris kosong
    if (tpl.bahan.length === 0) {
        bahanContainer.appendChild(buatBahanRow(bahanIndex++));
    }

    updateHapusBtn();
}

jenisPorsiSel.addEventListener('change', () => renderTemplateList(templateSearch.value));
templateSearch.addEventListener('input', () => renderTemplateList(templateSearch.value));

// Reset saat modal dibuka
document.getElementById('tambahMenuModal').addEventListener('show.bs.modal', () => {
    templateSearch.value = '';
    renderTemplateList();
    bahanContainer.innerHTML = '';
    bahanIndex = 0;
    bahanContainer.appendChild(buatBahanRow(bahanIndex++));
    updateHapusBtn();
    namaMenuInput.value = '';
    jenisPorsiSel.value = '';
});

// Init
renderTemplateList();

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

function openAkgModal(tanggal, tanggalFmt) {
    document.getElementById('inputTanggalHarian').value = tanggal;
    document.getElementById('akgTanggalTitle').textContent = tanggalFmt;
    
    const modal = new bootstrap.Modal(document.getElementById('modalAkgHarian'));
    modal.show();
}
</script>
@endsection