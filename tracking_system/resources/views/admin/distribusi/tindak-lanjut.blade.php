@extends('layouts.admin')
@section('title', 'Kelola Distribusi Mingguan')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Kelola Distribusi Mingguan</h4>
                <span class="text-muted">
                    Minggu mulai: {{ \Carbon\Carbon::parse($distribusi->tanggal_awal)->format('d M Y') }}
                </span>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <form action="{{ route('admin.distribusi.total.simpan') }}" method="POST">
                @csrf
                <input type="hidden" name="id_distribusi" value="{{ $distribusi->id }}">

                @php
                    $pagu = $pagu;
                    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $start = \Carbon\Carbon::parse($distribusi->tanggal_distribusi);
                @endphp

                <div class="accordion" id="accordionDistribusi">
                    @foreach($hariList as $index => $tanggalStr)
                        @php
                            $tanggal = \Carbon\Carbon::parse($tanggalStr);
                            $namaHari = $tanggal->locale('id')->dayName; // Senin, Selasa, dst
                        @endphp

                        <div class="accordion-item mb-3 border shadow-sm">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button collapsed fw-bold bg-light" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $index }}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse{{ $index }}">
                                    {{ $namaHari }}, {{ $tanggal->format('d M Y') }}
                                    <span class="ms-auto me-3 text-muted summary-preview" data-tanggal="{{ $tanggalStr }}">
                                        <!-- Placeholder default, akan di-update JS -->
                                        Penerima: 0 | Pagu: Rp 0
                                    </span>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse" 
                                 aria-labelledby="heading{{ $index }}" 
                                 data-bs-parent="#accordionDistribusi">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        @foreach($sekolahAktif as $s)
                                        @php
                                            $data = $dataDistribusi[$tanggalStr][$s->id] ?? null;

                                            // Prioritas: DB > old > default sekolah
                                            $porsiKecil = old("sekolah.{$s->id}.{$tanggalStr}.porsi_kecil_harian", 
                                                            $data?->porsi_kecil_harian ?? $s->porsi_kecil_default);

                                            $porsiBesar = old("sekolah.{$s->id}.{$tanggalStr}.porsi_besar_harian", 
                                                            $data?->porsi_besar_harian ?? $s->porsi_besar_default);

                                            $total = $porsiKecil + $porsiBesar;

                                            // Gunakan pagu dari DB kalau sudah tersimpan, kalau belum hitung ulang
                                            $paguSekolah = $data?->pagu_harian_sekolah 
                                                        ?? (($porsiKecil * $pagu->pagu_porsi_kecil) + ($porsiBesar * $pagu->pagu_porsi_besar));

                                            $jenisMenu = $data?->menu_kering > 0 ? 'kering' : ($data?->menu_basah > 0 ? 'basah' : 'kering');
                                        @endphp

                                        <div class="col-12 mb-3 border-bottom pb-3 sekolah-row" 
                                            data-sekolah="{{ $s->id }}" 
                                            data-hari="{{ $tanggalStr }}">
                                            <strong>{{ $s->nama_sekolah }}</strong>
                                            <input type="hidden" name="sekolah[{{ $s->id }}][{{ $tanggalStr }}][id_sekolah]" value="{{ $s->id }}">
                                            <input type="hidden" name="sekolah[{{ $s->id }}][{{ $tanggalStr }}][tanggal_harian]" value="{{ $tanggalStr }}">

                                            <div class="row g-3 mt-2">
                                                <div class="col-md-2">
                                                    <label>Porsi Kecil</label>
                                                    <input type="number"
                                                        name="sekolah[{{ $s->id }}][{{ $tanggalStr }}][porsi_kecil_harian]"
                                                        class="form-control porsi kecil-input"
                                                        min="0"
                                                        value="{{ $porsiKecil }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Porsi Besar</label>
                                                    <input type="number"
                                                        name="sekolah[{{ $s->id }}][{{ $tanggalStr }}][porsi_besar_harian]"
                                                        class="form-control porsi besar-input"
                                                        min="0"
                                                        value="{{ $porsiBesar }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Total Penerima</label>
                                                    <input type="number" class="form-control total" value="{{ $total }}" readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Pagu Sekolah</label>
                                                    <input type="text" class="form-control pagu-sekolah bg-light" 
                                                        value="Rp {{ number_format($paguSekolah, 0, ',', '.') }}" readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Jenis Menu</label>
                                                    <div class="d-flex gap-3 mt-2">
                                                        <div class="form-check">
                                                            <input type="radio" name="sekolah[{{ $s->id }}][{{ $tanggalStr }}][jenis_menu]"
                                                                value="kering" {{ $jenisMenu === 'kering' ? 'checked' : '' }} required>
                                                            <label>Kering</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="radio" name="sekolah[{{ $s->id }}][{{ $tanggalStr }}][jenis_menu]"
                                                                value="basah" {{ $jenisMenu === 'basah' ? 'checked' : '' }}>
                                                            <label>Basah</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>

                                    <!-- Summary Total per Hari (real-time) -->
                                    <div class="mt-4 p-3 bg-info-subtle border border-info rounded summary-harian" data-tanggal="{{ $tanggalStr }}">
                                        <h6 class="fw-bold mb-3 text-info">Total Penerima Manfaat - {{ $namaHari }} {{ $tanggal->format('d M Y') }}</h6>
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <strong>Porsi Kecil</strong><br>
                                                <span class="fs-5 total-kecil">0</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Porsi Besar</strong><br>
                                                <span class="fs-5 total-besar">0</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Pagu Harian</strong><br>
                                                <span class="fs-5 text-success fw-bold total-pagu">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-end mt-5">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-save me-2"></i> Simpan Semua Data Distribusi Mingguan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update summary per hari + preview di header accordion
function updateSummaryHarian(tanggal) {
    let totalKecil = 0;
    let totalBesar = 0;

    document.querySelectorAll(`[name*="sekolah"][name*="[${tanggal}][porsi_kecil_harian]"]`).forEach(el => {
        totalKecil += parseInt(el.value) || 0;
    });

    document.querySelectorAll(`[name*="sekolah"][name*="[${tanggal}][porsi_besar_harian]"]`).forEach(el => {
        totalBesar += parseInt(el.value) || 0;
    });

    const paguKecil = {{ $pagu->pagu_porsi_kecil }};
    const paguBesar = {{ $pagu->pagu_porsi_besar }};
    const paguHarian = (totalKecil * paguKecil) + (totalBesar * paguBesar);

    // Update summary di dalam collapse
    const summaryContainer = document.querySelector(`.summary-harian[data-tanggal="${tanggal}"]`);
    if (summaryContainer) {
        summaryContainer.querySelector('.total-kecil').textContent = totalKecil;
        summaryContainer.querySelector('.total-besar').textContent = totalBesar;
        summaryContainer.querySelector('.total-pagu').textContent = 'Rp ' + paguHarian.toLocaleString('id-ID');
    }

    // Update preview di header accordion (saat collapse)
    const previewEl = document.querySelector(`.summary-preview[data-tanggal="${tanggal}"]`);
    if (previewEl) {
        previewEl.textContent = `Penerima: ${totalKecil + totalBesar} | Pagu: Rp ${paguHarian.toLocaleString('id-ID')}`;
    }
}

// Event listener untuk semua input porsi
document.querySelectorAll('.porsi').forEach(input => {
    input.addEventListener('input', function() {
        const tanggal = this.closest('.sekolah-row')?.dataset.hari;
        if (tanggal) {
            updateSummaryHarian(tanggal);
        }

        // Update total per sekolah
        const row = this.closest('.row');
        if (row) {
            const kecil = parseInt(row.querySelector('.kecil-input')?.value) || 0;
            const besar = parseInt(row.querySelector('.besar-input')?.value) || 0;
            row.querySelector('.total').value = kecil + besar;

            // Update pagu sekolah secara real-time
            const paguKecil = {{ $pagu->pagu_porsi_kecil }};
            const paguBesar = {{ $pagu->pagu_porsi_besar }};
            const paguSekolah = (kecil * paguKecil) + (besar * paguBesar);
            row.querySelector('.pagu-sekolah').value = 'Rp ' + paguSekolah.toLocaleString('id-ID');
        }
    });
});

// Inisialisasi semua summary saat load (termasuk preview header)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.summary-harian').forEach(container => {
        const tanggal = container.dataset.tanggal;
        if (tanggal) {
            updateSummaryHarian(tanggal);
        }
    });
});
</script>
@endsection