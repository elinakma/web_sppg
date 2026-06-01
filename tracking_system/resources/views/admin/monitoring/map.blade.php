@extends('layouts.admin')

@section('title', 'Monitoring Driver - Peta Real-time')

@section('styles')
    <style>
.map-wrapper {
    position: relative;
    height: 520px;
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
 
.badge-driver {
    background-color: #e3f2fd;
    color: #0d6efd;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
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
 
/* Legend */
.map-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: center;
}
 
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #555;
}
 
.legend-line {
    width: 28px;
    height: 4px;
    border-radius: 2px;
}
 
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px rgba(0,0,0,.2);
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
                        Kelola Pemantauan
                    </li>
                </ol>
            </nav>

            <hr style="margin-top: 10px; margin-bottom: 20px;">

            <!-- List driver -->
            <h5 class="mb-3 fw-semibold">Daftar Driver</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="60">No</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Email</th>
                            <th>Jumlah Sekolah</th>
                            <th width="220">Aksi</th>
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

                                <td class="text-center">
                                    @if($driver->sedang_berjalan)
                                        <span class="badge status-badge d-inline-block"
                                            style="background-color:#198754;">
                                            <i class="bi bi-truck"></i> Sedang Berjalan
                                        </span>
                                    @else
                                        <span class="badge status-badge d-inline-block"
                                            style="background-color:#6c757d;">
                                            <i class="bi bi-pause-circle"></i> Tidak Berjalan
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">{{ $driver->email }}</td>

                                <td class="text-center">
                                    <span class="badge status-badge d-inline-block bg-info">
                                        {{ $driver->assignedSekolah->count() }} Sekolah
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="action-group">
                                        <button class="soft-btn btn-next"
                                                data-bs-toggle="modal"
                                                data-bs-target="#assignModal"
                                                data-driver-id="{{ $driver->id }}"
                                                data-driver-name="{{ $driver->name }}"
                                                data-assigned-sekolah="{{ json_encode($driver->assignedSekolah->pluck('id')->toArray()) }}">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                    </div>
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
                <h5 class="mb-3 fw-semibold">List Pengiriman Hari Ini</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="60">No</th>
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
                                    <td class="text-center">{{ $item->driverPengirim?->name ?? '-' }}</td>
                                    </td>
                                    <td class="text-center">{{ $item->sekolah?->nama_sekolah ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($item->tanggal_harian)->locale('id')->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge status-badge d-inline-block 
                                            @if($item->status == 'selesai') bg-success
                                            @elseif($item->status == 'dikirim') bg-warning text-dark
                                            @else bg-primary
                                            @endif">
                                            {{ $item->status == 'draf' ? 'Draf' : ($item->status == 'dikirim' ? 'Dikirim' : 'Selesai') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
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

    <!-- Peta Monitoring -->
    <div class="card driver-table-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff;">
            <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Pemantauan Perjalanan</h5>
            <span class="small" id="lastUpdate">Menunggu data...</span>
        </div>
 
        <div class="card-body p-3 pb-2">
            <!-- Legend -->
            <div class="map-legend mb-2">
                <div class="legend-item">
                    <div class="legend-line" style="background:#2563eb;"></div>
                    <span>Jalur Perjalanan</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#f59e0b;"></div>
                    <span>Posisi driver</span>
                </div>
            </div>
 
            <!-- MAP -->
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
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-gear me-2"></i>Atur Sekolah untuk
                        <span id="modalDriverName">-</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-divider"></div>

                <form action="{{ route('admin.monitoring.assign.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="driver_id" id="modalDriverId">

                    <div class="modal-body">
                        <div class="card mb-4 border-0 bg-success bg-opacity-10">
                            <div class="card-body py-2">
                                <h6 class="mb-0 fw-semibold text-success">
                                    Pilih Sekolah Tujuan Pengiriman
                                </h6>
                            </div>
                        </div>

                        <div class="row g-3">
                            @if(isset($sekolahSemua) && $sekolahSemua->isNotEmpty())
                                @foreach($sekolahSemua as $sekolah)
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
                                                    @if($sekolah->status !== 'Aktif')
                                                        <span class="badge bg-secondary ms-2">Nonaktif</span>
                                                    @endif
                                                </label>
                                            </div>

                                            <span class="badge status-badge bg-warning d-none conflict-badge" style="font-size: 10px;">
                                                Sudah dipakai
                                            </span>

                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <div class="alert alert-light border text-muted text-center">
                                        Tidak ada sekolah tersedia.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"
                            style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;">
                            Simpan
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
 
const driverEndMarker   = {}; // marker posisi terkini (kuning)
const driverStartMarker = {}; // marker titik awal (hijau)
const driverPolylines   = {}; // garis biru jalur (road route)
const driverPoints      = {}; // orange checkpoint dots (raw GPS)
 
let hasInitialFit = false;
let addressCache  = {};
const routeCache  = {};

function iconGreen() {
    return L.icon({
        iconUrl   : 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl : 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize  : [25, 41],
        iconAnchor: [12, 41],
    });
}
 
function iconYellow() {
    return L.icon({
        iconUrl   : 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
        shadowUrl : 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize  : [25, 41],
        iconAnchor: [12, 41],
    });
}

document.addEventListener('DOMContentLoaded', function () {
    map = L.map('map').setView([-7.629, 111.523], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom     : 19,
    }).addTo(map);
 
    updateAll();
    setInterval(updateAll, 10000);
});

async function updateAll() {
    try {
        const res     = await fetch('{{ route('api.drivers.locations') }}');
        const drivers = await res.json();
 
        const active = drivers.filter(d => d.location && d.is_online);
        if (active.length > 0) {
            const now = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
            document.getElementById('lastUpdate').textContent = `Diperbarui: ${now} WIB`;
        } else {
            document.getElementById('lastUpdate').textContent = 'Tidak ada driver aktif';
        }
 
        for (const driver of drivers) {
            await updateTrackingLayer(driver);
        }
 
        if (!hasInitialFit && Object.keys(driverEndMarker).length > 0) {
            const all = [
                ...Object.values(driverEndMarker),
                ...Object.values(driverStartMarker),
            ].filter(Boolean);
            map.fitBounds(L.featureGroup(all).getBounds(), { padding: [50, 50] });
            hasInitialFit = true;
        }
 
    } catch (err) {
        console.error('[Monitoring] Gagal update:', err);
    }
}

async function buildRoadRoute(rawCoords) {
    if (rawCoords.length < 2) return rawCoords;
 
    // Signature untuk cache: awal|akhir|jumlah titik
    const f   = rawCoords[0];
    const l   = rawCoords[rawCoords.length - 1];
    const sig = `${f[0].toFixed(5)},${f[1].toFixed(5)}|${l[0].toFixed(5)},${l[1].toFixed(5)}|${rawCoords.length}`;
    if (routeCache[sig]) return routeCache[sig];
 
    // Sampling max 25 waypoint (temenmu sampling max 50 ke ORS)
    const MAX_WP  = 25;
    let waypoints = rawCoords;
    if (waypoints.length > MAX_WP) {
        const step    = Math.ceil(waypoints.length / MAX_WP);
        const sampled = [];
        for (let i = 0; i < waypoints.length; i += step) sampled.push(waypoints[i]);
        if (sampled[sampled.length - 1] !== waypoints[waypoints.length - 1]) {
            sampled.push(waypoints[waypoints.length - 1]);
        }
        waypoints = sampled;
    }
 
    try {
        // OSRM format: longitude,latitude dipisah ;
        const coordStr = waypoints.map(c => `${c[1]},${c[0]}`).join(';');
        const url      = `https://router.project-osrm.org/route/v1/driving/${coordStr}?overview=full&geometries=geojson`;
 
        const res  = await fetch(url, { signal: AbortSignal.timeout(7000) });
        const data = await res.json();
 
        if (data.code === 'Ok' && data.routes?.[0]?.geometry?.coordinates?.length) {
            // Konversi [lng, lat] → [lat, lng] untuk Leaflet
            const road = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
            routeCache[sig] = road;
            return road;
        }
    } catch (_) {}
 
    // Fallback: raw GPS coords jika OSRM gagal
    return rawCoords;
}

async function updateTrackingLayer(driver) {
    const uid = driver.id;
 
    // Driver offline → hapus semua layer (sama seperti temenmu)
    if (!driver.is_online) {
        if (driverEndMarker[uid])   { map.removeLayer(driverEndMarker[uid]);   delete driverEndMarker[uid]; }
        if (driverStartMarker[uid]) { map.removeLayer(driverStartMarker[uid]); delete driverStartMarker[uid]; }
        if (driverPolylines[uid])   { map.removeLayer(driverPolylines[uid]);   delete driverPolylines[uid]; }
        if (driverPoints[uid])      { map.removeLayer(driverPoints[uid]);       delete driverPoints[uid]; }
        return;
    }
 
    try {
        const res    = await fetch(`/api/drivers/${uid}/history`);
        const points = await res.json();
 
        if (!points || points.length < 1) return;
 
        points.sort((a, b) => new Date(a.tracked_at) - new Date(b.tracked_at));
 
        const rawCoords = points.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);
        const lastIdx   = rawCoords.length - 1;
 
        // Rute jalan yang sudah di-snap ke jalan asli dari OSRM (biru) atau fallback ke raw GPS (tanpa snap)
        let roadCoords = rawCoords;
        if (rawCoords.length >= 2) {
            roadCoords = await buildRoadRoute(rawCoords);
        }
 
        // Polyline jalur perjalanan (biru)
        if (roadCoords.length >= 2) {
            if (driverPolylines[uid]) {
                driverPolylines[uid].setLatLngs(roadCoords);
            } else {
                driverPolylines[uid] = L.polyline(roadCoords, {
                    color       : '#2563eb',
                    weight      : 5,
                    opacity     : 0.9,
                    lineJoin    : 'round',
                    lineCap     : 'round',
                    smoothFactor: 1,
                }).addTo(map);
            }
        }
 
        // Checkpoint dots untuk titik raw GPS (orange)
        if (!driverPoints[uid]) {
            driverPoints[uid] = L.layerGroup().addTo(map);
        }
        driverPoints[uid].clearLayers();
 
        rawCoords.forEach((c, idx) => {
            if (idx === 0 || idx === lastIdx) return;
 
            const pt    = points[idx];
            const popId = `chk-${uid}-${idx}`;
 
            const dot = L.circleMarker(c, {
                radius     : 4,
                weight     : 1.5,
                color      : '#ff7a00',
                fillColor  : '#ff7a00',
                fillOpacity: 0.85,
            });
 
            dot.bindPopup(`
                <div style="font-size:12px;min-width:170px">
                    <b>📍 Checkpoint #${idx}</b><br>
                    <span style="color:#555">${pt.tracked_at ?? '-'}</span><br>
                    <span id="${popId}">Klik untuk lihat alamat...</span>
                </div>
            `);
 
            dot.on('popupopen', async () => {
                const el = document.getElementById(popId);
                if (!el || el.dataset.loaded) return;
                el.dataset.loaded = '1';
                el.textContent    = 'Memuat alamat...';
                const addr = await getAddress(c[0], c[1]);
                const el2  = document.getElementById(popId);
                if (el2) el2.textContent = addr;
            });
 
            dot.addTo(driverPoints[uid]);
        });
 
        // Marker titik awal - hijau
        if (!driverStartMarker[uid]) {
            driverStartMarker[uid] = L.marker(rawCoords[0], { icon: iconGreen() })
                .addTo(map)
                .bindPopup(`
                    <div style="font-size:12px">
                        <b>📍 Titik Awal</b><br>
                        <span style="color:#555">${points[0].tracked_at ?? '-'}</span>
                    </div>
                `);
        } else {
            driverStartMarker[uid].setLatLng(rawCoords[0]);
        }
 
        // Marker posisi sekarang - kuning
        if (!driver.location) return;
 
        const loc   = driver.location;
        const lat   = parseFloat(loc.latitude);
        const lng   = parseFloat(loc.longitude);
        const stats = driver.tracking_stats || {};
 
        if (driverEndMarker[uid]) {
            driverEndMarker[uid].setLatLng([lat, lng]);
            driverEndMarker[uid].setPopupContent(buildPopup(driver, stats, loc.created_at, 'Memuat alamat...'));
        } else {
            driverEndMarker[uid] = L.marker([lat, lng], { icon: iconYellow() })
                .addTo(map)
                .bindPopup(buildPopup(driver, stats, loc.created_at, 'Memuat alamat...'));
        }
 
        const address = await getAddress(lat, lng);
        if (driverEndMarker[uid]) {
            driverEndMarker[uid].setPopupContent(buildPopup(driver, stats, loc.created_at, address));
        }
 
    } catch (_) {}
}

async function getAddress(lat, lng) {
    const key = `${lat.toFixed(4)},${lng.toFixed(4)}`;
    if (addressCache[key]) return addressCache[key];
 
    try {
        const res  = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
            { headers: { 'Accept-Language': 'id' } }
        );
        const data = await res.json();
        if (data?.display_name) {
            addressCache[key] = data.display_name;
            return data.display_name;
        }
    } catch (_) {}
 
    return 'Lokasi tidak diketahui';
}

function buildPopup(driver, stats, lastUpdated, address) {
    const updatedStr = new Date(lastUpdated)
        .toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
 
    return `
        <div style="min-width:220px;font-size:13px;line-height:1.6">
            <div style="font-weight:700;font-size:14px;margin-bottom:4px">
                🚚 ${driver.name}
            </div>
            <div style="color:#555;margin-bottom:6px">${driver.email}</div>
            <hr style="margin:6px 0">
            <div>📍 <b>Alamat:</b><br><span style="color:#374151">${address}</span></div>
            <hr style="margin:6px 0">
            <div>📌 Titik tracking: ${stats.point_count ?? '-'}</div>
            <div>🛣️ Total jarak: ${stats.total_km ?? '-'} km</div>
            <hr style="margin:6px 0">
            <div style="color:#888;font-size:11px">⏱ ${updatedStr} WIB</div>
        </div>
    `;
}

const assignedSekolahRaw = @json($assignedSekolah);
const assignedSekolah = {};

// Konversi key dan value assignedSekolah menjadi integer
Object.keys(assignedSekolahRaw).forEach(driverId => {
    assignedSekolah[driverId] = assignedSekolahRaw[driverId].map(id => parseInt(id));
});

// Konversi seluruh allAssigned menjadi array of integer
const allAssigned = (@json($allAssignedSekolah) || []).map(id => parseInt(id));
 
document.querySelectorAll('[data-bs-target="#assignModal"]').forEach(btn => {
    btn.addEventListener('click', function () {
        const driverId   = parseInt(this.getAttribute('data-driver-id')); // Konversi ke Int
        const driverName = this.getAttribute('data-driver-name');
 
        document.getElementById('modalDriverId').value         = driverId;
        document.getElementById('modalDriverName').textContent = driverName;
 
        const driverAssigned = assignedSekolah[driverId] || [];
 
        document.querySelectorAll('.sekolah-checkbox').forEach(cb => {
            const sekolahId = parseInt(cb.value); // Sudah berupa Int
            const card      = cb.closest('.sekolah-card');
            const badge     = card.querySelector('.conflict-badge');
 
            // Reset state default awal modal dibuka
            cb.checked  = false;
            cb.disabled = false;
            card.classList.remove('checked', 'disabled');
            badge.classList.add('d-none');
 
            // Cek status kepemilikan sekolah
            if (driverAssigned.includes(sekolahId)) {
                cb.checked = true;
                card.classList.add('checked');
            } else if (allAssigned.includes(sekolahId)) {
                cb.disabled = true;
                card.classList.add('disabled');
                badge.classList.remove('d-none'); // Badge "Sudah dipakai" akan muncul
            }
        });
    });
});
</script>
@endsection