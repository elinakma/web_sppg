@extends('layouts.admin')

@section('title', 'Monitoring Driver - Peta Real-time')

@section('styles')
    <style>
        .map-wrapper {
            position: relative;
            height: 600px;
            width: 100%;
            overflow: hidden;
        }

        #map {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            height: 100% !important;
            width: 100% !important;
        }

        .card-body {
            padding: 0 !important;
            margin: 0 !important;
            height: 600px !important; /* Force height */
        }

        .leaflet-container {
            background: #e0e0e0;
        }

        .leaflet-tile-pane img {
            image-rendering: crisp-edges !important;
            image-rendering: pixelated !important;
        }
    </style>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          crossorigin=""/>
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">Monitoring Posisi Driver Real-time</h4>
        </div>
        <div class="card-body">
            <!-- Wrapper khusus peta -->
            <div class="map-wrapper">
                <div id="map"></div>
            </div>
        </div>
        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
            <small>Update terakhir: {{ now()->format('d M Y H:i:s') }} WIB</small>
            <button class="btn btn-sm btn-secondary" onclick="location.reload()">Refresh Peta</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script>
        let map; // Variabel peta untuk akses awal
        let markers = {}; // Simpan marker per driver (key: user_id)

        document.addEventListener('DOMContentLoaded', function () {
            map = L.map('map').setView([-7.629, 111.523], 12); // membuat peta dengan view awal dan posisi awal peta dan level zoom sesuai setView

            // Memasang tile layer OpenStreetMap dengan penggabungan bagian dan atribut
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map); // menambahkan tile layer ke variabel map

            // Fungsi untuk fetch & update lokasi
            function updateLocations() {
                // request data lokasi driver dari API
                fetch('{{ route('api.drivers.locations') }}')
                    .then(response => response.json())
                    .then(data => {
                        // Loop proses ambil data setiap driver
                        data.forEach(driver => {
                            if (driver.locations.length > 0) { 
                                const loc = driver.locations[0]; 
                                const lat = loc.latitude;
                                const lng = loc.longitude;
                                const userId = driver.id;

                                // Kalau marker sudah ada, updat e posisi
                                if (markers[userId]) {
                                    markers[userId].setLatLng([lat, lng]);
                                    markers[userId].setPopupContent(`
                                        <b>Driver: ${driver.name}</b><br>
                                        Email: ${driver.email}<br>
                                        Lokasi terakhir: ${new Date(loc.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })} WIB<br>
                                        Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}
                                    `);
                                } else {
                                    // Buat marker baru
                                    const marker = L.marker([lat, lng]).addTo(map);
                                    marker.bindPopup(`
                                        <b>Driver: ${driver.name}</b><br>
                                        Email: ${driver.email}<br>
                                        Lokasi terakhir: ${new Date(loc.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })} WIB<br>
                                        Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}
                                    `);
                                    markers[userId] = marker;
                                }
                            }
                        });

                        // Auto fit kalau ada marker
                        if (Object.keys(markers).length > 0) {
                            const group = L.featureGroup(Object.values(markers));
                            map.fitBounds(group.getBounds(), { padding: [50, 50] });
                        }
                    })
                    .catch(error => console.error('Gagal fetch lokasi:', error));
            }

            // Jalankan pertama kali
            updateLocations();

            // Refresh otomatis setiap 5 detik
            setInterval(updateLocations, 1000);
        });
    </script>
@endsection