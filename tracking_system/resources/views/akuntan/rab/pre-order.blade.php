@extends('layouts.admin')

@section('title', 'Pre Order RAB')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none fw-semibold" style="color: #133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Pre-Order</li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Awal</label>
                    <input type="date" id="tanggal_awal" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Akhir</label>
                    <input type="date" id="tanggal_akhir" class="form-control" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" onclick="cetakPDF()" 
                            class="btn btn-primary px-4">
                        <i class="bi bi-printer me-2"></i> Cetak
                    </button>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                Pilih periode tanggal, kemudian klik tombol "Cetak" untuk mengonversi ke dalam PDF.
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function cetakPDF() {
    const tglAwal = document.getElementById('tanggal_awal').value;
    const tglAkhir = document.getElementById('tanggal_akhir').value;

    if (!tglAwal || !tglAkhir) {
        alert('Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu!');
        return;
    }

    if (tglAwal > tglAkhir) {
        alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('akuntan.rab.export-pdf') }}";

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    form.innerHTML = `
        <input type="hidden" name="_token" value="${token}">
        <input type="hidden" name="tanggal_awal" value="${tglAwal}">
        <input type="hidden" name="tanggal_akhir" value="${tglAkhir}">
    `;

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endsection