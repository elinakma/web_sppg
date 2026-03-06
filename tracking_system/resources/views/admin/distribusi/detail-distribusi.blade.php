@extends('layouts.admin')

@section('title', 'Detail Distribusi Mingguan')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Detail Distribusi Mingguan</h4>
            <a href="{{ route('admin.distribusi.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card-body">
            <!-- Info Minggu -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="fw-bold">Periode Minggu</h5>
                    <p>
                        {{ \Carbon\Carbon::parse($distribusi->tanggal_awal)->format('d M Y') }} 
                        s/d 
                        {{ \Carbon\Carbon::parse($distribusi->tanggal_akhir)->format('d M Y') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold">Status</h5>
                    <p>
                        <span class="badge bg-info text-dark fs-6">
                            {{ ucfirst($distribusi->status) }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Tabel Summary per Hari -->
            <h5 class="fw-bold mb-3">Ringkasan Penerima & Pagu Harian</h5>
            <div class="table-responsive mb-5">
                <table class="table table-bordered table-hover">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Hari</th>
                            <th>Tanggal</th>
                            <th>Total Porsi Kecil</th>
                            <th>Total Porsi Besar</th>
                            <th>Total Penerima</th>
                            <th>Pagu Harian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hariList as $tanggalStr)
                            @php
                                $tanggal = \Carbon\Carbon::parse($tanggalStr);
                                $namaHari = $tanggal->locale('id')->dayName;
                                $summary = $summaryHarian[$tanggalStr] ?? [
                                    'total_porsi_kecil' => 0,
                                    'total_porsi_besar' => 0,
                                    'pagu_harian'       => 0,
                                ];
                            @endphp
                            <tr>
                                <td>{{ $namaHari }}</td>
                                <td>{{ $tanggal->format('d M Y') }}</td>
                                <td class="text-center">{{ $summary['total_porsi_kecil'] }}</td>
                                <td class="text-center">{{ $summary['total_porsi_besar'] }}</td>
                                <td class="text-center">{{ $summary['total_porsi_kecil'] + $summary['total_porsi_besar'] }}</td>
                                <td class="text-end fw-bold text-success">
                                    Rp {{ number_format($summary['pagu_harian'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Grand Total Pagu Mingguan</td>
                            <td class="text-end text-success">
                                Rp {{ number_format($grandTotalPagu, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Detail per Sekolah per Hari -->
            <h5 class="fw-bold mb-3">Detail per Sekolah</h5>
            <div class="accordion" id="accordionDetailSekolah">
                @foreach($sekolahAktif as $s)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSekolah{{ $s->id }}">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseSekolah{{ $s->id }}">
                                <i class="bi bi-building me-2"></i> {{ $s->nama_sekolah }}
                            </button>
                        </h2>
                        <div id="collapseSekolah{{ $s->id }}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Porsi Kecil</th>
                                                <th>Porsi Besar</th>
                                                <th>Total Penerima</th>
                                                <th>Pagu Sekolah</th>
                                                <th>Jenis Menu</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hariList as $tanggalStr)
                                                @php
                                                    $tanggal = \Carbon\Carbon::parse($tanggalStr);
                                                    $data = $dataDistribusi[$tanggalStr][$s->id] ?? null;
                                                    $porsiKecil = $data?->porsi_kecil_harian ?? $s->porsi_kecil_default;
                                                    $porsiBesar = $data?->porsi_besar_harian ?? $s->porsi_besar_default;
                                                    $total = $porsiKecil + $porsiBesar;
                                                    $paguSekolah = ($porsiKecil * $pagu->pagu_porsi_kecil) + ($porsiBesar * $pagu->pagu_porsi_besar);
                                                    $jenisMenu = $data?->menu_kering > 0 ? 'Kering' : ($data?->menu_basah > 0 ? 'Basah' : '-');
                                                @endphp
                                                <tr>
                                                    <td>{{ $tanggal->format('d M Y') }}</td>
                                                    <td class="text-center">{{ $porsiKecil }}</td>
                                                    <td class="text-center">{{ $porsiBesar }}</td>
                                                    <td class="text-center">{{ $total }}</td>
                                                    <td class="text-end">Rp {{ number_format($paguSekolah, 0, ',', '.') }}</td>
                                                    <td class="text-center">{{ $jenisMenu }}</td>
                                                    <td>{{ $data?->keterangan ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tombol Cetak -->
            <div class="text-end mt-4">
                <a href="{{ route('admin.distribusi.berita-acara', $distribusi->id) }}" class="btn btn-success btn-lg" target="_blank">
                    <i class="bi bi-printer me-2"></i> Cetak Semua Berita Acara
                </a>
            </div>
        </div>
    </div>
</div>
@endsection