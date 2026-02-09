@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tracking Map</h1>
    <div id="map" style="height: 500px;"></div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-7.0, 110.0], 5);  // Koordinat default Indonesia
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Tambah marker dari data drivers
    @foreach($drivers as $driver)
        @if($driver->locations->last())
            var lat = {{ $driver->locations->last()->latitude }};
            var lng = {{ $driver->locations->last()->longitude }};
            L.marker([lat, lng]).addTo(map).bindPopup('Driver: {{ $driver->name }}');
        @endif
    @endforeach
</script>
@endpush