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
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            {{-- ── Header & Action (Hidden when printing) ──────────────── --}}
            <div class="d-flex justify-content-between align-items-end mb-4 d-print-none">
                <div>
                    <h5 class="fw-bold m-0" style="color: #0f172a; letter-spacing: -0.5px;">Rekapitulasi Distribusi</h5>
                    <p class="text-muted small mb-0">Laporan periode distribusi pangan.</p>
                </div>
            </div>

            {{-- ── Filter Area (Hidden when printing) ──────────────────── --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3 d-print-none" style="background: #ffffff;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route(strtolower(Auth::user()->role) . '.rekap.index') }}" class="row g-2 align-items-end">
                        
                        <div class="col-md-7">
                            <label class="text-muted fw-bold mb-1" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;">Pilih Periode Laporan</label>
                            <select name="id_distribusi" class="form-select form-select-sm border-light shadow-none rounded-2" onchange="this.form.submit()" style="font-size: 0.85rem; height: 38px;">
                                <option value="">-- Cari Periode --</option>
                                @foreach($periodeList as $periode)
                                    @php
                                        $labelOpt = \Carbon\Carbon::parse($periode->tanggal_awal)->locale('id')->isoFormat('D MMM Y')
                                                . ' – ' . \Carbon\Carbon::parse($periode->tanggal_akhir)->locale('id')->isoFormat('D MMM Y');
                                    @endphp
                                    <option value="{{ $periode->id }}" {{ $selectedId == $periode->id ? 'selected' : '' }}>
                                        {{ $periode->nama_distribusi ?? $labelOpt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold rounded-2" style="height: 38px; font-size: 0.8rem; background: #2563eb;">
                                    <i class="bi bi-filter me-1"></i> Filter Data
                                </button>
                                
                                @if($rekapData && $distribusi)
                                <a href="{{ route(strtolower(Auth::user()->role) . '.rekap.cetak', $distribusi->id) }}" 
                                class="btn btn-danger btn-sm px-4 fw-bold rounded-2 d-flex align-items-center"
                                style="height: 38px;"
                                target="_blank">
                                    <i class="bi bi-file-earmark-pdf-fill me-2"></i> 
                                    <span class="d-none d-sm-inline">Cetak PDF</span>
                                </a>
                                @endif
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            @if($rekapData)
            @php
                $rab = $rekapData['rabHarian'];
                $menuHarian = $rekapData['menuHarian'];
                $totalKebutuhan = $rekapData['totalKebutuhan'];
                $totalPaguHarian = $rekapData['totalPaguHarian'];
                $totalSelisih = $rekapData['totalSelisih'];
                $porsiKecil = $rekapData['porsiKecil'];
                $porsiBesar = $rekapData['porsiBesar'];
                $totalPenerima = $rekapData['totalPenerima'];
                $jumlahSekolah = $rekapData['jumlahSekolah'];
            @endphp

            {{-- ── PRINTABLE AREA ──────────────────────────────────────── --}}
            <div id="printable-area" class="card border-0 shadow-lg rounded-1 overflow-hidden" style="background: #fff;">
                
                {{-- Simple Header --}}
                <div class="px-5 py-4 border-bottom border-light">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem; letter-spacing: -0.2px;">
                                Laporan Rekapitulasi Distribusi Pangan
                            </h5>
                            <p class="text-muted small mb-0">
                                Periode: <span class="text-primary fw-bold">{{ $rekapData['tglAwalFmt'] }} - {{ $rekapData['tglAkhirFmt'] }}</span>
                            </p>
                        </div>
                        
                        <div class="col-4 text-end">
                            <div class="d-flex justify-content-end gap-3">
                                
                                <!-- Total Penerima Manfaat -->
                                <div class="text-center">
                                    <div class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Total PM</div>
                                    <div class="fw-bold" style="font-size: 0.85rem;">{{ $totalPenerima }}</div>
                                </div>

                                <!-- Jumlah Sekolah -->
                                <div class="text-center border-start ps-3">
                                    <div class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Sekolah</div>
                                    <div class="fw-bold" style="font-size: 0.85rem;">{{ $jumlahSekolah }}</div>
                                </div>

                                <!-- Porsi Kecil -->
                                <div class="text-center border-start ps-3">
                                    <div class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Porsi Kecil</div>
                                    <div class="fw-bold text-info" style="font-size: 0.85rem;">
                                        {{ number_format($porsiKecil ?? 0) }}
                                    </div>
                                </div>

                                <!-- Porsi Besar -->
                                <div class="text-center border-start ps-3">
                                    <div class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Porsi Besar</div>
                                    <div class="fw-bold text-warning" style="font-size: 0.85rem;">
                                        {{ number_format($porsiBesar ?? 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-5 pt-3">
                    
                    {{-- Bagian I: Financial --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="fw-bold text-dark me-2" style="font-size: 0.65rem; letter-spacing: 1.2px;">01. RANCANGAN ANGGARAN BIAYA</span>
                            <div class="flex-grow-1 border-bottom border-light"></div>
                        </div>
                        <table class="table table-sm align-middle mb-0" style="font-size: 0.75rem;">
                            <thead>
                                <tr class="text-muted border-bottom" style="font-size: 0.6rem; text-transform: uppercase;">
                                    <th class="py-2 border-0">Hari</th>
                                    <th class="py-2 border-0 text-end">Estimasi Biaya</th>
                                    <th class="py-2 border-0 text-end">Pagu Anggaran</th>
                                    <th class="py-2 border-0 text-end">Efisiensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rab as $row)
                                <tr class="border-bottom border-light-subtle">
                                    <td class="py-2 fw-bold text-dark">{{ strtoupper($row['nama_hari']) }}</td>
                                    <td class="py-2 text-end text-muted">Rp {{ number_format($row['kebutuhan'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-end text-muted">Rp {{ number_format($row['pagu_harian'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-end fw-bold {{ $row['selisih'] < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $row['selisih'] < 0 ? '-' : '+' }} Rp {{ number_format(abs($row['selisih']), 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc;">
                                    <td class="py-3 ps-2 fw-bold text-dark">GRAND TOTAL</td>
                                    <td class="py-3 text-end fw-bold text-dark">Rp {{ number_format($totalKebutuhan, 0, ',', '.') }}</td>
                                    <td class="py-3 text-end fw-bold text-dark">Rp {{ number_format($totalPaguHarian, 0, ',', '.') }}</td>
                                    <td class="py-3 text-end fw-bold {{ $totalSelisih < 0 ? 'text-danger' : 'text-primary' }}" style="font-size: 0.8rem;">
                                        Rp {{ number_format($totalSelisih, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Bagian II: Meal Plan --}}
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="fw-bold text-dark me-2" style="font-size: 0.65rem; letter-spacing: 1.2px;">02. MENU</span>
                            <div class="flex-grow-1 border-bottom border-light"></div>
                        </div>
                        <div class="row g-3">
                            @foreach($menuHarian as $hariMenu)
                            <div class="col-4">
                                <div class="p-3 border rounded-1 h-100" style="background: #fafafa;">
                                    <h6 class="fw-bold text-dark mb-2 border-bottom pb-1" style="font-size: 0.8rem;">{{ strtoupper($hariMenu['nama_hari']) }}</h6>
                                    
                                    <div class="mb-2">
                                        <div class="text-primary fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Porsi Kecil ({{ $porsiKecil }})</div>
                                        @foreach($hariMenu['menu_kecil'] as $item)
                                            <div class="{{ str_contains($item, '·') ? 'text-muted ps-1' : 'fw-semibold text-dark' }}" style="font-size: 0.7rem; line-height: 1.2;">
                                                {{ $item }}
                                            </div>
                                        @endforeach
                                    </div>

                                    <div>
                                        <div class="text-warning fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Porsi Besar ({{ $porsiBesar }})</div>
                                        @foreach($hariMenu['menu_besar'] as $item)
                                            <div class="{{ str_contains($item, '·') ? 'text-muted ps-1' : 'fw-semibold text-dark' }}" style="font-size: 0.7rem; line-height: 1.2;">
                                                {{ $item }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @else
            <div class="py-5 text-center">
                <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">Silakan pilih periode distribusi.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    body { background-color: #f8fafc; }

    /* PERBAIKAN CETAK: Sembunyikan elemen sidebar & navbar admin secara paksa */
    @media print {
        /* Sembunyikan elemen bawaan template admin */
        header, footer, aside, .sidebar, .navbar, .main-header, .main-footer, 
        .breadcrumb, .nav-tabs, .btn-primary, .d-print-none {
            display: none !important;
        }

        /* Atur agar main content memenuhi layar */
        body, .main-content, .content-wrapper, .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-width: 100% !important;
            background: white !important;
        }

        /* Hilangkan box shadow dan border saat cetak */
        .card { box-shadow: none !important; border: none !important; }
        #printable-area { 
            border: none !important; 
            padding: 0 !important;
            margin: 0 !important;
        }

        @page { 
            size: A4; 
            margin: 1.5cm; 
        }
    }

    .table-sm td, .table-sm th { padding: 0.5rem; }
    .border-light-subtle { border-color: #f1f5f9 !important; }
</style>
@endsection