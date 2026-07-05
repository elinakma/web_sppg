import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, StyleSheet, RefreshControl, ScrollView, Alert, ActivityIndicator
} from 'react-native';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import WebView from 'react-native-webview';
import { getAslapDriversWithHistory, getDriverHistory } from '../../utils/api';
import { SafeAreaView } from 'react-native-safe-area-context';

export default function AslapMonitoringScreen({ navigation }) {
  const [drivers, setDrivers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [mapReady, setMapReady] = useState(false);

  const webRef = useRef(null);
  const intervalRef = useRef(null);
  const routeCache = useRef({});
  const addressCache = useRef({});
  const mapReadyRef = useRef(false);
  const driversRef = useRef([]);

  // ================== GET ADDRESS ==================
  const getAddress = async (lat, lng) => {
    if (!lat || !lng) return 'Lokasi tidak tersedia';
    const cacheKey = `${parseFloat(lat).toFixed(6)},${parseFloat(lng).toFixed(6)}`;
    if (addressCache.current[cacheKey]) return addressCache.current[cacheKey];

    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
        {
          headers: {
            'Accept-Language': 'id',
            'User-Agent': 'TrackingSystemApp/1.0',
            'Accept': 'application/json'
          }
        }
      );
      const data = await response.json();
      if (data?.display_name) {
        addressCache.current[cacheKey] = data.display_name;
        return data.display_name;
      }
    } catch (_) {}
    return 'Alamat tidak dapat diambil';
  };

  const injectToMap = (processed) => {
    const active = processed.filter(d => d.is_online && d.history?.length > 0);
    if (active.length > 0 && webRef.current && mapReadyRef.current) {
      webRef.current.injectJavaScript(`
        if (window.updateDrivers) {
          window.updateDrivers(${JSON.stringify(active)});
        }
        true;
      `);
    }
  };

  // ================== FETCH DATA ==================
  const fetchAll = async (isBackground = false) => {
    if (!isBackground) setLoading(true);
    setErrorMsg('');

    try {
      const data = await getAslapDriversWithHistory();

      const processed = await Promise.all(
        data.map(async (driver) => {
          let rawHistory = [];
          let snappedRoute = [];

          if (driver.is_online && driver.location) {
            rawHistory = await getDriverHistory(driver.id);
            if (rawHistory.length >= 2) {
              snappedRoute = await processRoute(rawHistory, driver.id); // FIX #5: tambah driver.id
            } else {
              snappedRoute = rawHistory.map(p => ({
                latitude: parseFloat(p.latitude),
                longitude: parseFloat(p.longitude),
              }));
            }
          }

          const address = driver.location?.latitude && driver.location?.longitude
            ? await getAddress(driver.location.latitude, driver.location.longitude)
            : null;

          return { ...driver, rawHistory, history: snappedRoute, address };
        })
      );

      setDrivers(processed);
      driversRef.current = processed;

      injectToMap(processed);

    } catch (error) {
      console.error(error);
      if (error.response?.status === 401 || error.message?.includes('token')) {
        Alert.alert('Sesi Habis', 'Silakan login kembali', [
          { text: 'OK', onPress: () => navigation.replace('Login') }
        ]);
      }
      setErrorMsg('Gagal memuat data monitoring');
    } finally {
      if (!isBackground) setLoading(false);
      setRefreshing(false);
    }
  };

  // ================== OSRM ==================
  const processRoute = async (points, driverId = 0) => {
    const rawCoords = points.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);
    const sig = `${driverId}|${rawCoords[0][0].toFixed(5)},${rawCoords[0][1].toFixed(5)}|${rawCoords.at(-1)[0].toFixed(5)},${rawCoords.at(-1)[1].toFixed(5)}|${rawCoords.length}`;

    if (routeCache.current[sig]) return routeCache.current[sig];

    try {
      let waypoints = rawCoords;
      const MAX_WP = 25;
      if (waypoints.length > MAX_WP) {
        const step = Math.ceil(waypoints.length / MAX_WP);
        waypoints = waypoints.filter((_, i) => i % step === 0 || i === waypoints.length - 1);
      }

      const coordStr = waypoints.map(([lat, lng]) => `${lng},${lat}`).join(';');
      const url = `https://router.project-osrm.org/route/v1/driving/${coordStr}?overview=full&geometries=geojson&steps=false`;
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 7000);

      let res;
      try {
        res = await fetch(url, { signal: controller.signal });
      } finally {
        clearTimeout(timeout);
      }

      const data = await res.json();

      if (data.code === 'Ok' && data.routes?.[0]?.geometry?.coordinates?.length > 1) {
        const road = data.routes[0].geometry.coordinates.map(([lng, lat]) => ({
          latitude: lat,
          longitude: lng,
        }));
        routeCache.current[sig] = road;
        return road;
      }
    } catch (e) {
      console.warn('OSRM gagal, fallback ke raw GPS:', e.message);
    }

    return rawCoords.map(([lat, lng]) => ({ latitude: lat, longitude: lng }));
  };

  useEffect(() => {
    fetchAll();
    intervalRef.current = setInterval(() => fetchAll(true), 20000);
    return () => clearInterval(intervalRef.current);
  }, []);

  useEffect(() => {
    if (!mapReady) return;
    mapReadyRef.current = true;
    if (driversRef.current.length > 0) {
      injectToMap(driversRef.current);
    }
  }, [mapReady]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchAll();
  };

  if (loading && drivers.length === 0) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#0d6efd" />
        <Text style={styles.loadingText}>Memuat peta monitoring...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}>
        <Text style={styles.title}>Pemantauan Perjalanan</Text>

        {errorMsg && <View style={styles.errorBox}><Text style={styles.errorText}>{errorMsg}</Text></View>}

        {/* Leaflet Map */}
        <View style={styles.mapContainer}>
          <WebView
            ref={webRef}
            originWhitelist={['*']}
            source={{ html: getLeafletHTML() }}
            style={styles.map}
            javaScriptEnabled={true}
            onLoad={() => setMapReady(true)}
            onError={() => setErrorMsg('Gagal memuat peta')}
            scrollEnabled={false}
            nestedScrollEnabled={true}
            onTouchStart={(e) => e.stopPropagation()}
          />
        </View>

        {/* Daftar Driver */}
        <Text style={styles.subtitle}>Daftar Driver ({drivers.length})</Text>

        <View style={{ paddingHorizontal: 16 }}>
          {drivers.map(driver => (
            <View key={driver.id} style={styles.driverCard}>
              <View style={styles.driverHeader}>
                <Icon name="account" size={28} color="#374151" />
                <View style={{ marginLeft: 12, flex: 1 }}>
                  <Text style={styles.driverName}>{driver.name}</Text>
                  <Text style={styles.email}>{driver.email}</Text>
                </View>
                {driver.sedang_berjalan !== undefined && (
                  <View style={[styles.trackingBadge, { backgroundColor: driver.sedang_berjalan ? '#dcfce7' : '#f3f4f6' }]}>
                    <Icon
                      name={driver.sedang_berjalan ? 'truck-delivery' : 'pause-circle'}
                      size={14}
                      color={driver.sedang_berjalan ? '#16a34a' : '#6b7280'}
                    />
                    <Text style={[styles.trackingText, { color: driver.sedang_berjalan ? '#16a34a' : '#6b7280' }]}>
                      {driver.sedang_berjalan ? 'Sedang Berjalan' : 'Tidak Berjalan'}
                    </Text>
                  </View>
                )}
              </View>

              {driver.address ? (
                <Text style={styles.locationText}>📍 {driver.address}</Text>
              ) : driver.location ? (
                <Text style={styles.locationText}>
                  📍 {parseFloat(driver.location.latitude).toFixed(5)}, {parseFloat(driver.location.longitude).toFixed(5)}
                </Text>
              ) : null}

              {driver.tracking_stats && (
                <Text style={styles.stats}>
                  🛣️ {driver.tracking_stats.total_km} km • {driver.tracking_stats.point_count} titik
                </Text>
              )}
            </View>
          ))}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

// ================== LEAFLET HTML ==================
const getLeafletHTML = () => `
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    body { margin:0; padding:0; }
    #map { height: 100vh; width: 100%; }
    .custom-marker { background: none; border: none; font-size: 24px; }
  </style>
</head>
<body>
  <div id="map"></div>
  <script>
    const map = L.map('map').setView([-7.55, 111.52], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const driverLayers = {}; // FIX #7: track per-driver agar update smooth tanpa kedip

    window.updateDrivers = function(drivers) {
      const incomingIds = new Set(drivers.map(d => String(d.id)));

      // Hapus layer driver yang sudah tidak ada / offline
      Object.keys(driverLayers).forEach(id => {
        if (!incomingIds.has(id)) {
          driverLayers[id].forEach(layer => map.removeLayer(layer));
          delete driverLayers[id];
        }
      });

      drivers.forEach(driver => {
        if (!driver.history || driver.history.length < 1) return;

        const id = String(driver.id);
        const coords = driver.history.map(p => [p.latitude, p.longitude]);

        // Hapus layer lama milik driver ini saja (bukan semua)
        if (driverLayers[id]) {
          driverLayers[id].forEach(layer => map.removeLayer(layer));
        }
        driverLayers[id] = [];

        // Polyline rute
        const polyline = L.polyline(coords, { color: '#2563eb', weight: 6, opacity: 0.85 }).addTo(map);
        driverLayers[id].push(polyline);

        // Marker titik awal
        const start = L.marker(coords[0], {
          icon: L.divIcon({ html: '🚩', iconSize: [32, 32], className: 'custom-marker' })
        }).addTo(map).bindPopup('Titik Awal - ' + driver.name);
        driverLayers[id].push(start);

        // Marker posisi terkini
        if (coords.length > 1) {
          const end = L.marker(coords[coords.length - 1], {
            icon: L.divIcon({ html: '🚚', iconSize: [32, 32], className: 'custom-marker' })
          }).addTo(map).bindPopup('Posisi Saat Ini - ' + driver.name);
          driverLayers[id].push(end);
        }
      });

      // Fit bounds ke semua rute yang aktif
      const allLayers = Object.values(driverLayers).flat();
      if (allLayers.length > 0) {
        try {
          map.fitBounds(L.featureGroup(allLayers).getBounds(), { padding: [60, 60] });
        } catch(_) {}
      }
    };
  </script>
</body>
</html>`;

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f7fb' },
  title: { fontSize: 22, fontWeight: 'bold', textAlign: 'center', marginVertical: 16, color: '#000' },
  subtitle: { fontSize: 18, fontWeight: '600', marginHorizontal: 16, marginVertical: 12, color: '#000' },
  mapContainer: {
    height: 380,
    margin: 16,
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#e5e7eb'
  },
  map: { flex: 1 },
  driverCard: { backgroundColor: '#fff', marginBottom: 12, padding: 16, borderRadius: 16, elevation: 2 },
  driverHeader: { flexDirection: 'row', alignItems: 'center' },
  driverName: { fontSize: 17, fontWeight: '700', color: '#000'  },
  email: { color: '#64748b', fontSize: 13 },
  trackingBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 20, gap: 4 },
  trackingText: { fontSize: 12, fontWeight: '600' },
  locationText: { marginTop: 8, fontSize: 14, color: '#374151', lineHeight: 20 },
  stats: { marginTop: 6, fontSize: 13, color: '#0ea5e9' },
  errorBox: { margin: 16, padding: 16, backgroundColor: '#fee2e2', borderRadius: 12 },
  errorText: { color: '#dc2626', textAlign: 'center' },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 10, color: '#6b7280' },
});