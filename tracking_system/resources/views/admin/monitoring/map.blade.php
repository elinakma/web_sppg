@extends('layouts.admin')

@section('title', 'Monitoring Driver - Peta Real-time')

@section('styles')
    <style>
    .map-wrapper {
        position: relative;
        height: 500px;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    #map {
        height: 100%;
        width: 100%;
    }

    .driver-table-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .table thead th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .table tbody tr:hover {
        background-color: #f2f6ff;
        transition: 0.2s ease-in-out;
    }

    .badge-driver {
        background-color: #e3f2fd;
        color: #0d6efd;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
    }

    .card-header {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .sekolah-card {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.25s ease;
        cursor: pointer;
        background: #fff;
        position: relative;
    }

    .sekolah-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 6px 16px rgba(0,0,0,0.05);
    }

    .sekolah-card.checked {
        border: 2px solid #0d6efd;
        background: #f4f9ff;
    }

    .sekolah-card.disabled {
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        cursor: not-allowed;
    }

    .sekolah-card.disabled label {
        color: #adb5bd;
    }

    .sekolah-checkbox {
        transform: scale(1.2);
        cursor: pointer;
    }

    .conflict-badge {
        font-size: 11px;
    }
    </style>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          crossorigin=""/>
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none fw-semibold" style="color: #133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page">
                        Kelola Pengiriman
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <!-- List driver -->
            <h6 class="mb-3 fw-semibold">Daftar Driver</h6>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th class="text-start">Driver</th>
                            <th>Status</th>
                            <th width="30%">Email</th>
                            <th width="15%">Jumlah Sekolah</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $index => $driver)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>

                                <td class="text-start">
                                    <div class="fw-semibold">{{ $driver->name }}</div>
                                    <span class="badge-driver">Driver Aktif</span>
                                </td>

                                <td class="text-center align-middle">
                                    @if($driver->sedang_berjalan)
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1"
                                            style="background-color:#198754;">
                                            <i class="bi bi-truck"></i> Sedang Berjalan
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1"
                                            style="background-color:#6c757d;">
                                            <i class="bi bi-pause-circle"></i> Tidak Berjalan
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center align-middle">{{ $driver->email }}</td>

                                <td class="text-center align-middle">
                                    <span class="badge bg-info">
                                        {{ $driver->assignedSekolah->count() }} Sekolah
                                    </span>
                                </td>

                                <td class="text-center align-middle">
                                    <button class="btn btn-sm btn-warning action-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assignModal"
                                            data-driver-id="{{ $driver->id }}"
                                            data-driver-name="{{ $driver->name }}"
                                            data-assigned-sekolah="{{ json_encode($driver->assignedSekolah->pluck('id')->toArray()) }}">
                                        <i class="bi bi-gear"></i> Tindak Lanjut
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Belum ada driver terdaftar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- List pengiriman -->
            <div class="mt-3">
                <h6 class="mb-3 fw-semibold">List Pengiriman Hari Ini</h6>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th>Driver</th>
                                <th>Sekolah</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pengirimanHariIni = \App\Models\DistribusiSekolah::whereDate('tanggal_harian', now()->toDateString())
                                    ->with('sekolah', 'driverPengirim')
                                    ->get();
                            @endphp

                            @forelse($pengirimanHariIni as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->driverPengirim?->name ?? '-' }}</td>
                                    </td>
                                    <td>{{ $item->sekolah?->nama_sekolah ?? '-' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($item->tanggal_harian)->format('d M Y') }} 
                                    </td>
                                    <td class="text-center">
                                        <span class="badge 
                                            @if($item->status == 'selesai') bg-success
                                            @elseif($item->status == 'dikirim') bg-warning text-dark
                                            @else bg-primary
                                            @endif">
                                            {{ $item->status == 'draf' ? 'Draf' : ($item->status == 'dikirim' ? 'Dikirim' : 'Selesai') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $item->waktu ? \Carbon\Carbon::parse($item->waktu)->timezone('Asia/Jakarta')->format('H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada pengiriman hari ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Peta -->
    <div class="card driver-table-card mb-4">
        <div class="card-header bg-gradient bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-truck me-2"></i>Monitoring Driver Real-time
            </h5>
            <span class="small" id="lastUpdate">Menunggu data...</span>
        </div>

        <div class="card-body p-4">
            <!-- MAP -->
            <h6 class="mb-3 fw-semibold">Peta Tracking</h6>

            <div class="map-wrapper">
                <div id="map"></div>
            </div>
        </div>

        <div class="card-footer text-end">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Modal Atur Sekolah -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        Atur Sekolah untuk Driver:
                        <span id="modalDriverName" class="fw-bold">-</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('admin.monitoring.assign.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="driver_id" id="modalDriverId">

                    <div class="modal-body">
                        <label class="form-label fw-bold mb-3">
                            Pilih Sekolah Tujuan Pengiriman
                        </label>

                        <div class="row g-3">
                            @if(isset($sekolahAktif) && $sekolahAktif->isNotEmpty())
                            @foreach($sekolahAktif as $sekolah)
                            <div class="col-md-6">
                                <div class="sekolah-card">
                                    <div class="d-flex align-items-center justify-content-between">
                                        
                                        <div class="form-check m-0 flex-grow-1">
                                            <input class="form-check-input me-3 sekolah-checkbox"
                                                type="checkbox"
                                                name="sekolah_ids[]"
                                                value="{{ $sekolah->id }}"
                                                id="sekolah{{ $sekolah->id }}">

                                            <label class="form-check-label fw-semibold"
                                                for="sekolah{{ $sekolah->id }}">
                                                {{ $sekolah->nama_sekolah }}
                                            </label>
                                        </div>

                                        <span class="badge bg-warning text-dark d-none conflict-badge">
                                            Sudah dipakai
                                        </span>

                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="col-12">
                                <div class="alert alert-light border text-muted text-center">
                                    Tidak ada sekolah aktif.
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script>
        let map;
        let markers = {};
        let hasInitialFit = false;
        let addressCache = {};

        document.addEventListener('DOMContentLoaded', function () {
            map = L.map('map').setView([-7.629, 111.523], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);

            // Fungsi reverse geocoding ke alamat
            async function getAddress(lat, lng) {
                const cacheKey = `${lat.toFixed(6)},${lng.toFixed(6)}`;
                if (addressCache[cacheKey]) {
                    return addressCache[cacheKey];
                }

                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14&addressdetails=1`,
                        {
                            headers: {
                                'Accept-Language': 'id'
                            }
                        }
                    );
                    const data = await response.json();

                    if (data && data.address) {
                        const address = data.address;
                        let parts = [];

                        // Jalan + nomor rumah
                        if (address.road || address.pedestrian || address.path || address.residential) {
                            let road = address.road || address.pedestrian || address.path || address.residential;
                            if (address.house_number) road += ` No. ${address.house_number}`;
                            parts.push(road);
                        }

                        // Desa/kelurahan/kampung
                        if (address.village || address.hamlet || address.suburb || address.neighbourhood) {
                            parts.push(address.village || address.hamlet || address.suburb || address.neighbourhood);
                        }

                        // Kecamatan
                        if (address.city_district || address.district) {
                            parts.push(address.city_district || address.district);
                        }

                        // Kota/kabupaten
                        if (address.city || address.town || address.county) {
                            parts.push(address.city || address.town || address.county);
                        }

                        // Provinsi
                        if (address.state || address.region) {
                            parts.push(address.state || address.region);
                        }

                        // Kode pos kalau ada
                        if (address.postcode) {
                            parts.push(`${address.postcode}`);
                        }

                        let formatted = parts.filter(Boolean).join(', ');
                        if (!formatted) formatted = data.display_name || 'Lokasi tidak teridentifikasi';

                        addressCache[cacheKey] = formatted;
                        return formatted;
                    }
                } catch (err) {
                    console.error('Gagal reverse geocode:', err);
                }

                return 'Lokasi tidak diketahui';
            }

            // Fungsi untuk fetch & update lokasi
            function updateLocations() {
                fetch('{{ route('api.drivers.locations') }}')
                    .then(response => response.json())
                    .then(async data => {
                        const activeDrivers = data.filter(d => d.locations.length > 0);

                        // Update teks hanya jika ada driver aktif
                        if (activeDrivers.length > 0) {
                            const now = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
                            document.getElementById('lastUpdate').textContent = `Update terakhir: ${now} WIB`;
                        } else {
                            document.getElementById('lastUpdate').textContent = 'Tidak ada driver aktif';
                        }
                        
                        // Loop proses ambil data setiap driver
                        for (const driver of data) {
                                if (driver.locations.length > 0) { 
                                    const loc = driver.locations[0]; 
                                    const lat = loc.latitude;
                                    const lng = loc.longitude;
                                    const userId = driver.id;
                                    const address = await getAddress(lat, lng);

                                    if (markers[userId]) {
                                        markers[userId].setLatLng([lat, lng]);
                                        markers[userId].setPopupContent(`
                                            <b>Driver: ${driver.name}</b><br>
                                            Email: ${driver.email}<br>
                                            Alamat: ${address}<br>
                                            Diperbarui terakhir: ${new Date(loc.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })} WIB<br>
                                        `);
                                    } else {
                                        const marker = L.marker([lat, lng]).addTo(map);
                                        marker.bindPopup(`
                                            <b>Driver: ${driver.name}</b><br>
                                            Email: ${driver.email}<br>
                                            Alamat: ${address}<br>
                                            Diperbarui terakhir: ${new Date(loc.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })} WIB<br>
                                        `);
                                        markers[userId] = marker;
                                    }
                                }
                        }
                        if (!hasInitialFit && Object.keys(markers).length > 0) {
                            const group = L.featureGroup(Object.values(markers));
                            map.fitBounds(group.getBounds(), { padding: [50, 50] });
                            hasInitialFit = true; 
                        }
                    })
                    .catch(error => console.error('Gagal fetch lokasi:', error));
            }

            // Jalankan pertama kali
            updateLocations();

            // Refresh otomatis setiap .. detik
            setInterval(updateLocations, 10000);
        });


        // Modal assign sekolah
        const assignedSekolah = @json($assignedSekolah);
        const allAssigned = @json($allAssignedSekolah);

        document.querySelectorAll('[data-bs-target="#assignModal"]').forEach(btn => {
            btn.addEventListener('click', function () {

                const driverId = this.getAttribute('data-driver-id');
                const driverName = this.getAttribute('data-driver-name');

                document.getElementById('modalDriverId').value = driverId;
                document.getElementById('modalDriverName').textContent = driverName;

                const driverAssigned = assignedSekolah[driverId] || [];

                document.querySelectorAll('.sekolah-checkbox').forEach(cb => {

                    const sekolahId = parseInt(cb.value);
                    const card = cb.closest('.sekolah-card');
                    const badge = card.querySelector('.conflict-badge');

                    cb.checked = false;
                    cb.disabled = false;

                    card.classList.remove('checked', 'disabled');
                    badge.classList.add('d-none');

                    // Milik driver ini
                    if (driverAssigned.includes(sekolahId)) {
                        cb.checked = true;
                        card.classList.add('checked');
                    }

                    // Milik driver lain
                    else if (allAssigned.includes(sekolahId)) {
                        cb.disabled = true;
                        card.classList.add('disabled');
                        badge.classList.remove('d-none');
                    }

                });
            });
        });
</script>
@endsection