@extends('layouts.admin')

@section('title', 'Rekap Distribusi')

@php
    $role = strtolower(auth()->user()->role ?? 'admin');
    $routePrefix = match($role) {
        'akuntan' => 'Akuntan',
        'gizi'    => 'Gizi',
        default   => 'Admin',
    };
@endphp

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">

    {{-- ── Breadcrumb ─────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route(strtolower(Auth::user()->role) . '.dashboard') }}"
                           class="text-decoration-none fw-semibold" style="color:#133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Rekap Distribusi</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- ── Filter Periode ─────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3" style="color:#133b84;">
                <i class="bi bi-funnel me-2"></i>Filter Periode Distribusi
            </h5>
            <form method="GET" action="{{ route(strtolower(Auth::user()->role) . '.rekap.index') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-semibold small text-muted">Pilih Periode</label>
                    <select name="id_distribusi" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periodeList as $periode)
                            @php
                                $labelOpt = \Carbon\Carbon::parse($periode->tanggal_awal)->locale('id')->isoFormat('D MMM Y')
                                          . ' – '
                                          . \Carbon\Carbon::parse($periode->tanggal_akhir)->locale('id')->isoFormat('D MMM Y');
                            @endphp
                            <option value="{{ $periode->id }}"
                                {{ $selectedId == $periode->id ? 'selected' : '' }}>
                                {{ $periode->nama_distribusi ?? $labelOpt }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Tampilkan Rekap
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($rekapData)
    @php
        $rab         = $rekapData['rabHarian'];
        $menuHarian  = $rekapData['menuHarian'];
        $totalKebutuhan  = $rekapData['totalKebutuhan'];
        $totalPaguHarian = $rekapData['totalPaguHarian'];
        $totalSelisih    = $rekapData['totalSelisih'];
        $porsiKecil      = $rekapData['porsiKecil'];
        $porsiBesar      = $rekapData['porsiBesar'];
        $totalPenerima   = $rekapData['totalPenerima'];
        $jumlahSekolah   = $rekapData['jumlahSekolah'];
        $tglAwalFmt      = $rekapData['tglAwalFmt'];
        $tglAkhirFmt     = $rekapData['tglAkhirFmt'];
    @endphp

    {{-- ═══════════════════════════════════════════════════════════
         LAPORAN UTAMA
    ════════════════════════════════════════════════════════════ --}}
    <div class="card shadow" id="rekap-cetak">
        <div class="card-body p-0">

            {{-- ── Header Laporan ──────────────────────────────── --}}
            <div class="text-center py-4 px-4 border-bottom" style="background:#fff;">
                <h4 class="fw-bold mb-1" style="color:#133b84; letter-spacing:.5px;">
                    REKAP DISTRIBUSI PANGAN
                </h4>
                <p class="mb-0 text-muted small">SPPG Dahlia Tambakromo</p>
            </div>

            <div class="px-4 py-3 border-bottom" style="background:#fff9f0;">
                <div class="row">
                    <div class="col-md-6">
                        <span class="text-muted small fw-semibold">Tanggal Mulai Periode</span><br>
                        <span class="fw-bold" style="color:#133b84;">{{ $tglAwalFmt }}</span>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="text-muted small fw-semibold">Tanggal Akhir Periode</span><br>
                        <span class="fw-bold" style="color:#133b84;">{{ $tglAkhirFmt }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">

                {{-- ════════════════════════════════════════════════
                     BAGIAN 1 – TABEL RAB
                ═════════════════════════════════════════════════ --}}
                <div class="mb-5">
                    <div class="section-label mb-3">
                        <span class="fw-bold px-3 py-1 rounded"
                              style="background:#133b84; color:#fff; font-size:.85rem; letter-spacing:1px;">
                            RAB
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="font-size:.9rem;">
                            <thead>
                                <tr style="background:#1a4a9e; color:#fff;">
                                    <th class="text-center py-2" style="width:15%;">HARI</th>
                                    <th class="text-center py-2" colspan="2">KEBUTUHAN</th>
                                    <th class="text-center py-2" colspan="2">PAGU HARIAN</th>
                                    <th class="text-center py-2" colspan="2">JUMLAH</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rab as $row)
                                @php
                                    $isDefisit = $row['selisih'] < 0;
                                    $noPagu    = $row['pagu_harian'] == 0;
                                    $noData    = $row['kebutuhan'] == 0 && $row['pagu_harian'] == 0;
                                @endphp
                                <tr style="background:#fff;">
                                    <td class="fw-bold text-center py-2"
                                        style="color:#133b84;">{{ $row['nama_hari'] }}</td>

                                    {{-- Kebutuhan --}}
                                    @if($noData)
                                        <td class="text-end text-muted" style="width:8%;">Rp</td>
                                        <td class="text-end text-muted" style="width:18%;">-</td>
                                    @else
                                        <td class="text-end text-muted" style="width:8%;">Rp</td>
                                        <td class="text-end fw-semibold" style="width:18%;">
                                            {{ number_format($row['kebutuhan'], 0, ',', '.') }}
                                        </td>
                                    @endif

                                    {{-- Pagu Harian --}}
                                    @if($noPagu)
                                        <td class="text-center" style="color:#c0392b; width:8%;">Rp</td>
                                        <td class="text-center fw-bold" style="color:#c0392b; width:14%;">#REF!</td>
                                    @else
                                        <td class="text-end text-muted" style="width:8%;">Rp</td>
                                        <td class="text-end fw-semibold" style="color:#27ae60; width:14%;">
                                            {{ number_format($row['pagu_harian'], 0, ',', '.') }}
                                        </td>
                                    @endif

                                    {{-- Selisih / Jumlah --}}
                                    @if($noPagu)
                                        <td class="text-center" style="color:#c0392b; width:8%;">Rp</td>
                                        <td class="text-center fw-bold" style="color:#c0392b; width:14%;">#REF!</td>
                                    @elseif($isDefisit)
                                        <td class="text-end" style="color:#c0392b; width:8%;">-Rp</td>
                                        <td class="text-end fw-bold" style="color:#c0392b; width:14%;">
                                            {{ number_format(abs($row['selisih']), 0, ',', '.') }}
                                        </td>
                                    @else
                                        <td class="text-end text-muted" style="width:8%;">Rp</td>
                                        <td class="text-end fw-bold" style="color:#27ae60; width:14%;">
                                            {{ number_format($row['selisih'], 0, ',', '.') }}
                                        </td>
                                    @endif
                                </tr>
                                @endforeach

                                {{-- Baris Total --}}
                                <tr style="background:#e8f0fb; border-top:2px solid #133b84;">
                                    <td class="fw-bold text-center py-2" style="color:#133b84;">TOTAL</td>
                                    <td class="text-end text-muted fw-bold">Rp</td>
                                    <td class="text-end fw-bold" style="color:#133b84;">
                                        {{ number_format($totalKebutuhan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-muted fw-bold">Rp</td>
                                    <td class="text-end fw-bold" style="color:#27ae60;">
                                        {{ number_format($totalPaguHarian, 0, ',', '.') }}
                                    </td>
                                    @if($totalSelisih < 0)
                                        <td class="text-end fw-bold" style="color:#c0392b;">-Rp</td>
                                        <td class="text-end fw-bold" style="color:#c0392b;">
                                            {{ number_format(abs($totalSelisih), 0, ',', '.') }}
                                        </td>
                                    @else
                                        <td class="text-end text-muted fw-bold">Rp</td>
                                        <td class="text-end fw-bold" style="color:#27ae60;">
                                            {{ number_format($totalSelisih, 0, ',', '.') }}
                                        </td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════════
                     BAGIAN 2 – MENU MAKANAN
                ═════════════════════════════════════════════════ --}}
                @if(count($menuHarian) > 0)
                <div class="mb-5">
                    <div class="section-label mb-3">
                        <span class="fw-bold px-3 py-1 rounded"
                              style="background:#133b84; color:#fff; font-size:.85rem; letter-spacing:1px;">
                            MENU
                        </span>
                    </div>

                    {{-- Grid 3 kolom per baris, mirip gambar referensi --}}
                    @php $chunks = array_chunk($menuHarian, 3); @endphp

                    @foreach($chunks as $baris)
                    <div class="row g-0 mb-0 border-start border-end
                                {{ !$loop->first ? '' : '' }}"
                         style="border: 1px solid #dee2e6;">
                        {{-- Header hari --}}
                        @foreach($baris as $hariMenu)
                        <div class="col border-end" style="min-width:0;">
                            <div class="text-center fw-bold py-2"
                                 style="background:#e87722; color:#fff; font-size:.85rem; letter-spacing:.5px;">
                                {{ $hariMenu['nama_hari'] }}
                            </div>
                        </div>
                        @endforeach
                        {{-- Isi menu --}}
                        @php $maxRows = max(array_map(fn($h) => max(count($h['menu_kecil']), count($h['menu_besar'])), $baris)); @endphp
                        <div class="row g-0 w-100">
                            @foreach($baris as $hariMenu)
                            <div class="col border-end p-2" style="min-width:0; font-size:.82rem;">
                                @if(count($hariMenu['menu_kecil']) > 0)
                                    <div class="mb-1">
                                        <span class="badge bg-info text-white mb-1" style="font-size:.7rem;">Porsi Kecil</span>
                                        @foreach($hariMenu['menu_kecil'] as $item)
                                            <div style="white-space: normal; word-break: break-word;
                                                        {{ str_starts_with(trim($item), '·') ? 'padding-left:10px; color:#555;' : 'font-weight:600; color:#1a3a6e;' }}">
                                                {{ $item }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(count($hariMenu['menu_besar']) > 0)
                                    <div>
                                        <span class="badge text-dark mb-1" style="font-size:.7rem; background:#f0c040;">Porsi Besar</span>
                                        @foreach($hariMenu['menu_besar'] as $item)
                                            <div style="white-space: normal; word-break: break-word;
                                                        {{ str_starts_with(trim($item), '·') ? 'padding-left:10px; color:#555;' : 'font-weight:600; color:#1a3a6e;' }}">
                                                {{ $item }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(count($hariMenu['menu_kecil']) === 0 && count($hariMenu['menu_besar']) === 0)
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3"></div>
                    @endforeach
                </div>
                @endif

                {{-- ════════════════════════════════════════════════
                     BAGIAN 3 – RINGKASAN PM (PENERIMA MANFAAT)
                ═════════════════════════════════════════════════ --}}
                <div>
                    <div class="section-label mb-3">
                        <span class="fw-bold px-3 py-1 rounded"
                              style="background:#133b84; color:#fff; font-size:.85rem; letter-spacing:1px;">
                            PM
                        </span>
                    </div>

                    <div class="table-responsive" style="max-width:420px;">
                        <table class="table table-bordered mb-0" style="font-size:.9rem;">
                            <tbody>
                                <tr style="background:#e8f0fb;">
                                    <td class="fw-bold py-2" style="color:#133b84; width:60%;">
                                        PORSI KECIL
                                    </td>
                                    <td class="text-end fw-bold py-2" style="color:#133b84;">
                                        {{ number_format($porsiKecil, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr style="background:#fff;">
                                    <td class="fw-bold py-2" style="color:#133b84;">
                                        PORSI BESAR
                                    </td>
                                    <td class="text-end fw-bold py-2" style="color:#133b84;">
                                        {{ number_format($porsiBesar, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr style="background:#e8f0fb;">
                                    <td class="fw-bold py-2" style="color:#133b84;">
                                        JUMLAH
                                    </td>
                                    <td class="text-end fw-bold py-2" style="color:#133b84;">
                                        {{ number_format($totalPenerima, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr style="background:#fff;">
                                    <td class="fw-bold py-2" style="color:#133b84;">
                                        Jumlah SEKOLAH
                                    </td>
                                    <td class="text-end fw-bold py-2" style="color:#133b84;">
                                        {{ $jumlahSekolah }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- end .p-4 --}}
        </div>{{-- end .card-body --}}
    </div>{{-- end #rekap-cetak --}}

    {{-- ── Tombol Cetak ────────────────────────────────────────── --}}
    <div class="text-end mt-3 d-print-none">
        <button onclick="cetakRekap()" class="btn btn-outline-secondary me-2">
            <i class="bi bi-printer me-1"></i> Cetak / PDF
        </button>
    </div>

    @else
    {{-- Belum ada data --}}
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
            <p class="mt-3 text-muted">
                Pilih periode distribusi di atas untuk menampilkan rekap.
            </p>
        </div>
    </div>
    @endif

</div>
@endsection

@section('styles')
<style>
    /* Cetak: sembunyikan sidebar, navbar, breadcrumb, filter, tombol */
    @media print {
        body * { visibility: hidden; }
        #rekap-cetak, #rekap-cetak * { visibility: visible; }
        #rekap-cetak { position: absolute; top: 0; left: 0; width: 100%; }
        .d-print-none { display: none !important; }

        table { border-collapse: collapse !important; }
        table td, table th { border: 1px solid #333 !important; }

        @page { margin: 1.5cm; size: A4 portrait; }
    }

    .section-label { border-left: 4px solid #133b84; padding-left: 8px; }
</style>
@endsection

@section('scripts')
<script>
    function cetakRekap() {
        window.print();
    }
</script>
@endsection