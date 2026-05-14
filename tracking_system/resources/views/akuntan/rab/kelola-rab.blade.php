@extends('layouts.admin')

@section('title', 'Kelola RAB')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none fw-semibold" style="color: #133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Kelola Rancangan Anggaran Biaya (RAB)</li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <h4 class="fw-bold mb-4">Rencana Anggaran Biaya (RAB)</h4>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ===================== ACCORDION PERIODE ===================== --}}
            <div class="accordion" id="accordionPeriode">

                @forelse($dataPerPeriode as $periodeIndex => $periodeData)
                @php
                    $distribusi        = $periodeData['distribusi'];
                    $hariDistribusi    = $periodeData['hariDistribusi'];
                    $menuPerTanggal    = $periodeData['menuPerTanggal'];
                    $totalPaguPeriode  = $periodeData['totalPaguPeriode'];
                    $totalHargaPeriode = $periodeData['totalHargaPeriode'];
                    $selisihPeriode    = $periodeData['selisihPeriode'];

                    $periodeId    = 'periode-' . $distribusi->id;
                    $tglAwalFmt   = \Carbon\Carbon::parse($distribusi->tanggal_awal)->locale('id')->isoFormat('D MMMM Y');
                    $tglAkhirFmt  = \Carbon\Carbon::parse($distribusi->tanggal_akhir)->locale('id')->isoFormat('D MMMM Y');
                    $labelPeriode = $distribusi->nama_distribusi ?? ($tglAwalFmt . ' – ' . $tglAkhirFmt);
                @endphp

                <div class="accordion-item mb-4 border rounded shadow-sm overflow-hidden">

                    {{-- ===== HEADER PERIODE ===== --}}
                    <h2 class="accordion-header" id="heading-{{ $periodeId }}">
                        <button class="accordion-button {{ $periodeIndex === 0 ? '' : 'collapsed' }} py-3"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ $periodeId }}"
                                aria-expanded="{{ $periodeIndex === 0 ? 'true' : 'false' }}"
                                aria-controls="collapse-{{ $periodeId }}"
                                style="background-color: #eef2fb;">
                            <div class="d-flex align-items-center gap-3 w-100 flex-wrap">
                                <div>
                                    <span class="badge rounded-pill text-white me-1" style="background-color:#133b84; font-size:.75rem;">
                                        <i class="bi bi-calendar-week me-1"></i> Periode 
                                        {{ $labelPeriode }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>

                    {{-- ===== BODY PERIODE ===== --}}
                    <div id="collapse-{{ $periodeId }}"
                         class="accordion-collapse collapse {{ $periodeIndex === 0 ? 'show' : '' }}"
                         aria-labelledby="heading-{{ $periodeId }}"
                         data-bs-parent="#accordionPeriode">
                        <div class="accordion-body p-3 p-md-4" style="background:#f9fafc;">

                            {{-- ========== ACCORDION HARI DALAM PERIODE ========== --}}
                            <div class="accordion" id="accordionHari-{{ $distribusi->id }}">

                                @foreach($hariDistribusi as $hariIndex => $hari)
                                @php
                                    $tgl       = \Carbon\Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
                                    $tglFmt    = \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM Y');
                                    $menuHari  = $menuPerTanggal->get($tgl, collect());
                                    $menuKecil = $menuHari->get('kecil', collect());
                                    $menuBesar = $menuHari->get('besar', collect());

                                    // Hanya tampilkan hari yang punya menu & bahan
                                    $adaBahan = $menuKecil->isNotEmpty() || $menuBesar->isNotEmpty();

                                    $paguHarian      = $hari->total_pagu_harian ?? 0;
                                    $totalHargaKecil = $menuKecil->sum(fn($m) =>
                                        $m->bahan->sum(fn($b) => ($b->jumlah ?? 0) * ($b->harga_satuan ?? 0))
                                    );
                                    $totalHargaBesar = $menuBesar->sum(fn($m) =>
                                        $m->bahan->sum(fn($b) => ($b->jumlah ?? 0) * ($b->harga_satuan ?? 0))
                                    );
                                    $totalHargaHari  = $totalHargaKecil + $totalHargaBesar;
                                    $selisihHari     = $paguHarian - $totalHargaHari;

                                    $hariAccId = 'hari-' . $distribusi->id . '-' . str_replace('-', '', $tgl);
                                @endphp

                                @if($adaBahan)
                                <div class="accordion-item mb-3 border rounded shadow-sm">

                                    {{-- Header Hari --}}
                                    <h2 class="accordion-header" id="heading-{{ $hariAccId }}">
                                        <button class="accordion-button {{ $hariIndex === 0 ? '' : 'collapsed' }} py-2"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse-{{ $hariAccId }}"
                                                aria-expanded="{{ $hariIndex === 0 ? 'true' : 'false' }}"
                                                aria-controls="collapse-{{ $hariAccId }}">
                                            <div class="d-flex align-items-center w-100 flex-wrap gap-2">
                                                <i class="bi bi-calendar-day me-2 text-primary"></i>
                                                <span class="fw-semibold">{{ $tglFmt }}</span>
                                                <div class="ms-auto d-flex gap-2 flex-wrap small" onclick="event.stopPropagation()">
                                                    <span class="text-muted">
                                                        Pagu:
                                                        <strong class="text-primary">Rp {{ number_format($paguHarian, 0, ',', '.') }}</strong>
                                                    </span>
                                                    <span class="text-muted">|</span>
                                                    <span class="text-muted">
                                                        Total:
                                                        <strong id="badge-hari-total-{{ $tgl }}"
                                                                class="{{ $totalHargaHari > $paguHarian ? 'text-danger' : 'text-success' }}">
                                                            Rp {{ number_format($totalHargaHari, 0, ',', '.') }}
                                                        </strong>
                                                    </span>
                                                    <span class="text-muted">|</span>
                                                    <span class="text-muted">
                                                        <span id="badge-hari-selisih-label-{{ $tgl }}">{{ $selisihHari >= 0 ? 'Sisa' : 'Defisit' }}</span>:
                                                        <strong id="badge-hari-selisih-{{ $tgl }}"
                                                                class="{{ $selisihHari < 0 ? 'text-danger' : 'text-success' }}">
                                                            Rp {{ number_format(abs($selisihHari), 0, ',', '.') }}
                                                        </strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>

                                    {{-- Body Hari --}}
                                    <div id="collapse-{{ $hariAccId }}"
                                         class="accordion-collapse collapse {{ $hariIndex === 0 ? 'show' : '' }}"
                                         aria-labelledby="heading-{{ $hariAccId }}"
                                         data-bs-parent="#accordionHari-{{ $distribusi->id }}">
                                        <div class="accordion-body p-3">

                                            <div class="row g-4">

                                                {{-- ---- PORSI KECIL ---- --}}
                                                @if($menuKecil->isNotEmpty())
                                                <div class="col-12">
                                                    <h6 class="mb-3">
                                                        <span class="badge bg-info">Porsi Kecil</span>
                                                        {{ $hari->total_porsi_kecil ?? 0 }} porsi
                                                    </h6>
                                                    @foreach($menuKecil as $menu)
                                                    <div class="mb-4">
                                                        <p class="fw-semibold mb-2">
                                                            <i class="bi bi-bowl-hot text-info"></i> {{ $menu->nama_menu }}
                                                        </p>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Bahan</th>
                                                                        <th class="text-center">Jumlah</th>
                                                                        <th class="text-center">Satuan</th>
                                                                        <th class="text-end">Harga Satuan (Rp)</th>
                                                                        <th class="text-end">Subtotal</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($menu->bahan as $bahan)
                                                                    <tr>
                                                                        <td>{{ $bahan->nama_bahan }}</td>
                                                                        <td class="text-center">
                                                                            {{ is_numeric($bahan->jumlah) ? rtrim(rtrim(number_format($bahan->jumlah, 2, ',', '.'), '0'), ',') : $bahan->jumlah }}
                                                                        </td>
                                                                        <td class="text-center">{{ $bahan->satuan }}</td>
                                                                        <td>
                                                                            <input type="text"
                                                                                class="form-control form-control-sm text-end harga-input"
                                                                                value="{{ number_format($bahan->harga_satuan, 0, ',', '.') }}"
                                                                                data-raw="{{ $bahan->harga_satuan }}"
                                                                                min="0"
                                                                                step="100"
                                                                                data-jumlah="{{ $bahan->jumlah }}"
                                                                                data-subtotal-id="subtotal-{{ $bahan->id }}"
                                                                                data-tanggal="{{ $tgl }}"
                                                                                data-periode-id="{{ $distribusi->id }}"
                                                                                data-pagu-periode="{{ $totalPaguPeriode }}"
                                                                                data-bahan-id="{{ $bahan->id }}">
                                                                        </td>
                                                                        <td class="text-end fw-semibold" id="subtotal-{{ $bahan->id }}">
                                                                            Rp {{ number_format(($bahan->jumlah ?? 0) * ($bahan->harga_satuan ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif

                                                {{-- ---- PORSI BESAR ---- --}}
                                                @if($menuBesar->isNotEmpty())
                                                <div class="col-12">
                                                    <h6 class="mb-3">
                                                        <span class="badge bg-warning text-dark">Porsi Besar</span>
                                                        {{ $hari->total_porsi_besar ?? 0 }} porsi
                                                    </h6>
                                                    @foreach($menuBesar as $menu)
                                                    <div class="mb-4">
                                                        <p class="fw-semibold mb-2">
                                                            <i class="bi bi-bowl-hot text-warning"></i> {{ $menu->nama_menu }}
                                                        </p>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Bahan</th>
                                                                        <th class="text-center">Jumlah</th>
                                                                        <th class="text-center">Satuan</th>
                                                                        <th class="text-end">Harga Satuan (Rp)</th>
                                                                        <th class="text-end">Subtotal</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($menu->bahan as $bahan)
                                                                    <tr>
                                                                        <td>{{ $bahan->nama_bahan }}</td>
                                                                        <td class="text-center">
                                                                            {{ is_numeric($bahan->jumlah) ? rtrim(rtrim(number_format($bahan->jumlah, 2, ',', '.'), '0'), ',') : $bahan->jumlah }}
                                                                        </td>
                                                                        <td class="text-center">{{ $bahan->satuan }}</td>
                                                                        <td>
                                                                            <input type="text"
                                                                                class="form-control form-control-sm text-end harga-input"
                                                                                value="{{ number_format($bahan->harga_satuan, 0, ',', '.') }}"
                                                                                data-raw="{{ $bahan->harga_satuan }}"
                                                                                min="0"
                                                                                step="100"
                                                                                data-jumlah="{{ $bahan->jumlah }}"
                                                                                data-subtotal-id="subtotal-{{ $bahan->id }}"
                                                                                data-tanggal="{{ $tgl }}"
                                                                                data-periode-id="{{ $distribusi->id }}"
                                                                                data-pagu-periode="{{ $totalPaguPeriode }}"
                                                                                data-bahan-id="{{ $bahan->id }}">
                                                                        </td>
                                                                        <td class="text-end fw-semibold" id="subtotal-{{ $bahan->id }}">
                                                                            Rp {{ number_format(($bahan->jumlah ?? 0) * ($bahan->harga_satuan ?? 0), 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif

                                            </div>{{-- end row --}}

                                            {{-- ===== Ringkasan Harian ===== --}}
                                            <div class="row g-3 mt-3">
                                                <div class="col-md-4">
                                                    <div class="card border-0 bg-light text-center py-3">
                                                        <div class="text-muted small">Pagu Harian</div>
                                                        <div class="fw-bold fs-6 text-primary"
                                                             id="pagu-{{ $tgl }}"
                                                             data-pagu="{{ $paguHarian }}">
                                                            Rp {{ number_format($paguHarian, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card border-0 text-center py-3 bg-success bg-opacity-10"
                                                         id="total-card-{{ $tgl }}">
                                                        <div class="text-muted small">Total Harga Bahan</div>
                                                        <div class="fw-bold fs-6 text-success" id="total-harga-{{ $tgl }}">
                                                            Rp {{ number_format($totalHargaHari, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card border-0 text-center py-3
                                                                {{ $selisihHari < 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' }}"
                                                         id="selisih-card-{{ $tgl }}">
                                                        <div class="text-muted small" id="selisih-label-{{ $tgl }}">
                                                            {{ $selisihHari >= 0 ? 'Sisa Pagu' : 'Defisit' }}
                                                        </div>
                                                        <div class="fw-bold fs-6 {{ $selisihHari < 0 ? 'text-danger' : 'text-success' }}"
                                                             id="selisih-{{ $tgl }}">
                                                            Rp {{ number_format(abs($selisihHari), 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Tombol Simpan Per Hari --}}
                                            <div class="text-end mt-3">
                                                <button type="button"
                                                        class="btn btn-primary btn-sm px-4"
                                                        onclick="simpanHargaPerTanggal('{{ $tgl }}')">
                                                    <i class="bi bi-save me-1"></i> Simpan Harga {{ \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('D MMM Y') }}
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>{{-- end accordion-item hari --}}
                                @endif

                                @endforeach {{-- end foreach hari --}}

                            </div>{{-- end accordionHari --}}

                            {{-- ===== Ringkasan Periode / Mingguan ===== --}}
                            <div class="card mt-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #eef2fb 0%, #f0f9ff 100%);">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color:#133b84;">
                                        <i class="bi bi-calculator me-2"></i>
                                        Ringkasan Periode: {{ $tglAwalFmt }} – {{ $tglAkhirFmt }}
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="card border-0 text-center py-3" style="background:#fff;">
                                                <div class="text-muted small mb-1">
                                                    <i class="bi bi-bank text-primary me-1"></i> Total Pagu Periode
                                                </div>
                                                <div class="fw-bold fs-5 text-primary"
                                                     id="ringkasan-pagu-{{ $distribusi->id }}"
                                                     data-pagu="{{ $totalPaguPeriode }}">
                                                    Rp {{ number_format($totalPaguPeriode, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border-0 text-center py-3"
                                                 id="ringkasan-total-card-{{ $distribusi->id }}"
                                                 style="background:#fff;">
                                                <div class="text-muted small mb-1">
                                                    <i class="bi bi-cart3 me-1"></i> Total Harga Bahan
                                                </div>
                                                <div class="fw-bold fs-5 {{ $totalHargaPeriode > $totalPaguPeriode ? 'text-danger' : 'text-success' }}"
                                                     id="ringkasan-total-{{ $distribusi->id }}">
                                                    Rp {{ number_format($totalHargaPeriode, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border-0 text-center py-3
                                                        {{ $selisihPeriode < 0 ? 'bg-danger bg-opacity-10' : '' }}"
                                                 id="ringkasan-selisih-card-{{ $distribusi->id }}"
                                                 style="{{ $selisihPeriode >= 0 ? 'background:#fff;' : '' }}">
                                                <div class="text-muted small mb-1"
                                                     id="ringkasan-selisih-label-{{ $distribusi->id }}">
                                                    <i class="bi bi-{{ $selisihPeriode < 0 ? 'exclamation-triangle text-danger' : 'check-circle text-success' }} me-1"></i>
                                                    {{ $selisihPeriode >= 0 ? 'Sisa Pagu Periode' : 'Defisit Periode' }}
                                                </div>
                                                <div class="fw-bold fs-5 {{ $selisihPeriode < 0 ? 'text-danger' : 'text-success' }}"
                                                     id="ringkasan-selisih-{{ $distribusi->id }}">
                                                    Rp {{ number_format(abs($selisihPeriode), 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- end ringkasan periode --}}

                        </div>
                    </div>
                </div>{{-- end accordion-item periode --}}

                @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Belum ada data RAB. Pastikan ahli gizi sudah menambahkan menu dan bahan makanan.
                </div>
                @endforelse

            </div>{{-- end accordionPeriode --}}

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // Helper convert format rupiah ke angka
    // contoh: 10.000 => 10000
    // ============================================================
    function angkaRupiah(nilai) {
        if (!nilai) return 0;

        return parseFloat(
            nilai.toString()
                .replace(/\./g, '')
                .replace(',', '.')
        ) || 0;
    }

    // ============================================================
    // Update ringkasan HARIAN
    // ============================================================
    function updateRingkasanHarian(tanggal) {
        let totalHari = 0;

        document.querySelectorAll(`.harga-input[data-tanggal="${tanggal}"]`).forEach(input => {
            const jumlah = parseFloat(input.dataset.jumlah) || 0;
            const harga  = angkaRupiah(input.value);

            totalHari += jumlah * harga;
        });

        const paguEl  = document.getElementById(`pagu-${tanggal}`);
        const pagu    = parseFloat(paguEl?.dataset.pagu) || 0;
        const selisih = pagu - totalHari;

        // Total Harga
        const totalEl = document.getElementById(`total-harga-${tanggal}`);
        if (totalEl) {
            totalEl.textContent = 'Rp ' + totalHari.toLocaleString('id-ID');
            totalEl.classList.toggle('text-danger', totalHari > pagu);
            totalEl.classList.toggle('text-success', totalHari <= pagu);
        }

        const totalCard = document.getElementById(`total-card-${tanggal}`);
        if (totalCard) {
            totalCard.classList.toggle('bg-danger', totalHari > pagu);
            totalCard.classList.toggle('bg-success', totalHari <= pagu);
            totalCard.classList.add('bg-opacity-10');
        }

        // Selisih
        const selisihEl    = document.getElementById(`selisih-${tanggal}`);
        const selisihLabel = document.getElementById(`selisih-label-${tanggal}`);
        const selisihCard  = document.getElementById(`selisih-card-${tanggal}`);

        if (selisihEl) {
            selisihEl.textContent = 'Rp ' + Math.abs(selisih).toLocaleString('id-ID');
            selisihEl.classList.toggle('text-danger', selisih < 0);
            selisihEl.classList.toggle('text-success', selisih >= 0);
        }

        if (selisihLabel) {
            selisihLabel.textContent = selisih >= 0 ? 'Sisa Pagu' : 'Defisit';
        }

        if (selisihCard) {
            selisihCard.classList.toggle('bg-danger', selisih < 0);
            selisihCard.classList.toggle('bg-success', selisih >= 0);
            selisihCard.classList.add('bg-opacity-10');
        }

        // Badge header hari
        const badgeTotal = document.getElementById(`badge-hari-total-${tanggal}`);
        if (badgeTotal) {
            badgeTotal.textContent = 'Rp ' + totalHari.toLocaleString('id-ID');
            badgeTotal.className = totalHari > pagu ? 'text-danger' : 'text-success';
        }

        const badgeSelisih = document.getElementById(`badge-hari-selisih-${tanggal}`);
        if (badgeSelisih) {
            badgeSelisih.textContent = 'Rp ' + Math.abs(selisih).toLocaleString('id-ID');
            badgeSelisih.className = selisih < 0 ? 'text-danger' : 'text-success';
        }

        const badgeLabel = document.getElementById(`badge-hari-selisih-label-${tanggal}`);
        if (badgeLabel) {
            badgeLabel.textContent = selisih >= 0 ? 'Sisa' : 'Defisit';
        }
    }

    // ============================================================
    // Update ringkasan PERIODE
    // ============================================================
    function updateRingkasanPeriode(periodeId) {

        let totalPeriode = 0;

        document.querySelectorAll(`.harga-input[data-periode-id="${periodeId}"]`).forEach(input => {
            const jumlah = parseFloat(input.dataset.jumlah) || 0;
            const harga  = angkaRupiah(input.value);

            totalPeriode += jumlah * harga;
        });

        const paguEl  = document.getElementById(`ringkasan-pagu-${periodeId}`);
        const pagu    = parseFloat(paguEl?.dataset.pagu) || 0;
        const selisih = pagu - totalPeriode;

        const totalEl = document.getElementById(`ringkasan-total-${periodeId}`);
        if (totalEl) {
            totalEl.textContent = 'Rp ' + totalPeriode.toLocaleString('id-ID');
            totalEl.classList.toggle('text-danger', totalPeriode > pagu);
            totalEl.classList.toggle('text-success', totalPeriode <= pagu);
        }

        const selisihEl = document.getElementById(`ringkasan-selisih-${periodeId}`);
        const labelEl   = document.getElementById(`ringkasan-selisih-label-${periodeId}`);

        if (selisihEl) {
            selisihEl.textContent = 'Rp ' + Math.abs(selisih).toLocaleString('id-ID');
            selisihEl.classList.toggle('text-danger', selisih < 0);
            selisihEl.classList.toggle('text-success', selisih >= 0);
        }

        if (labelEl) {
            labelEl.textContent = selisih >= 0 ? 'Sisa Pagu Periode' : 'Defisit Periode';
        }

        // Badge header periode
        const badgeTotal = document.getElementById(`badge-total-val-${periodeId}`);
        if (badgeTotal) {
            badgeTotal.textContent = totalPeriode.toLocaleString('id-ID');
        }

        const badgeSelisih = document.getElementById(`badge-selisih-val-${periodeId}`);
        if (badgeSelisih) {
            badgeSelisih.textContent = Math.abs(selisih).toLocaleString('id-ID');
        }

        const badgeLabel = document.getElementById(`badge-selisih-label-${periodeId}`);
        if (badgeLabel) {
            badgeLabel.textContent = selisih >= 0 ? 'Sisa' : 'Defisit';
        }
    }

    // ============================================================
    // Event input harga
    // ============================================================
    document.querySelectorAll('.harga-input').forEach(input => {

        input.addEventListener('input', function () {

            // format otomatis
            let angka = angkaRupiah(this.value);
            this.value = angka.toLocaleString('id-ID');

            // subtotal
            const subtotalEl = document.getElementById(this.dataset.subtotalId);

            if (subtotalEl) {
                const jumlah = parseFloat(this.dataset.jumlah) || 0;
                const total  = jumlah * angka;

                subtotalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }

            updateRingkasanHarian(this.dataset.tanggal);
            updateRingkasanPeriode(this.dataset.periodeId);
        });

    });

    // ============================================================
    // Init saat load page
    // ============================================================
    const tanggalList = new Set();
    const periodeList = new Set();

    document.querySelectorAll('.harga-input').forEach(input => {
        tanggalList.add(input.dataset.tanggal);
        periodeList.add(input.dataset.periodeId);
    });

    tanggalList.forEach(tanggal => updateRingkasanHarian(tanggal));
    periodeList.forEach(periode => updateRingkasanPeriode(periode));

    // ============================================================
    // Simpan harga
    // ============================================================
    window.simpanHargaPerTanggal = function (tanggal) {

        const formData = new FormData();

        document.querySelectorAll(`.harga-input[data-tanggal="${tanggal}"]`).forEach(input => {

            const bahanId = input.dataset.bahanId;
            const harga   = angkaRupiah(input.value);

            formData.append(`bahan[${bahanId}][id]`, bahanId);
            formData.append(`bahan[${bahanId}][harga_satuan]`, harga);
        });

        fetch("{{ route('akuntan.rab.harga.bulk') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {
                showToast('✅ Harga berhasil disimpan', 'success');
            } else {
                showToast('❌ Gagal simpan data', 'danger');
            }

        })
        .catch(() => {
            showToast('❌ Terjadi kesalahan', 'danger');
        });
    };

    // ============================================================
    // Toast
    // ============================================================
    function showToast(msg, type = 'success') {

        const div = document.createElement('div');

        div.className =
            `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3 shadow`;

        div.style.zIndex = '9999';
        div.innerHTML = msg;

        document.body.appendChild(div);

        setTimeout(() => div.remove(), 2500);
    }

});
</script>
@endsection