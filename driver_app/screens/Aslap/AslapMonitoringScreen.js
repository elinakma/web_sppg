import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, StyleSheet, RefreshControl,
  Alert, ActivityIndicator, ScrollView
} from 'react-native';
import MapView, { Marker } from 'react-native-maps';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { getAslapDriverLocations } from '../../utils/api';
import { SafeAreaView } from 'react-native-safe-area-context';

export default function AslapMonitoringScreen({ navigation }) {
  const [drivers, setDrivers]     = useState([]);
  const [loading, setLoading]     = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMsg, setErrorMsg]   = useState('');

  const intervalRef = useRef(null);

  const fetchData = async (isBackground = false) => {
    if (!isBackground) setLoading(true);
    setErrorMsg('');

    try {
      const data = await getAslapDriverLocations();
      console.log('[AslapMonitoring] Driver:', data.length, '| punya lokasi:', data.filter(d => d.has_location).length);
      setDrivers(data);
    } catch (error) {
      console.error('[AslapMonitoring] Error:', error);

      let message = 'Gagal memuat data monitoring';
      if (error.response?.status === 401 || error.message?.includes('token')) {
        message = 'Sesi expired. Silakan login kembali.';
        Alert.alert('Session Expired', message, [
          { text: 'OK', onPress: () => navigation.replace('Login') }
        ]);
      } else if (error.response?.status === 403) {
        message = 'Akses ditolak.';
      } else {
        message = error.response?.data?.message || error.message;
      }
      setErrorMsg(message);
    } finally {
      if (!isBackground) setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchData();
    intervalRef.current = setInterval(() => fetchData(true), 10000);
    return () => clearInterval(intervalRef.current);
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  // Driver yang sudah punya koordinat valid
  const driversWithLocation = drivers.filter(
    d => d.has_location && d.latitude !== null && d.longitude !== null
  );

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
      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        <Text style={styles.title}>Pemantauan Pengiriman</Text>

        {errorMsg ? (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMsg}</Text>
          </View>
        ) : (
          <>
            {/* ── MAP ─────────────────────────────────────── */}
            <View style={styles.mapContainer}>
              <MapView
                style={styles.map}
                initialRegion={{
                  latitude:      -7.55,
                  longitude:     111.52,
                  latitudeDelta:  0.8,
                  longitudeDelta: 0.8,
                }}
              >
                {driversWithLocation.map(driver => (
                  <Marker
                    key={driver.id}
                    coordinate={{
                      // latitude & longitude sudah flat dari api.js
                      latitude:  parseFloat(driver.latitude),
                      longitude: parseFloat(driver.longitude),
                    }}
                    title={driver.name}
                    description={
                      driver.tracked_at
                        ? `Update: ${new Date(driver.tracked_at).toLocaleTimeString('id-ID')}`
                        : 'Lokasi tersedia'
                    }
                  >
                    <View style={styles.markerContainer}>
                      <Icon
                        name="truck-delivery"
                        size={34}
                        color={driver.sedang_berjalan ? '#6366f1' : '#9ca3af'}
                      />
                    </View>
                  </Marker>
                ))}
              </MapView>

              {/* Info overlay jika belum ada driver dengan lokasi */}
              {driversWithLocation.length === 0 && (
                <View style={styles.mapOverlay}>
                  <Icon name="map-marker-off" size={28} color="#9ca3af" />
                  <Text style={styles.mapOverlayText}>
                    Belum ada driver yang aktif tracking
                  </Text>
                </View>
              )}
            </View>

            {/* ── LIST DRIVER ─────────────────────────────── */}
            <Text style={styles.subtitle}>
              Daftar Driver ({drivers.length})
            </Text>

            <View style={{ paddingHorizontal: 16 }}>
              {drivers.length === 0 ? (
                <View style={styles.emptyBox}>
                  <Icon name="account-off" size={32} color="#9ca3af" />
                  <Text style={styles.emptyText}>Belum ada data driver</Text>
                </View>
              ) : (
                drivers.map(driver => (
                  <View key={driver.id} style={styles.driverCard}>

                    <View style={styles.driverHeader}>
                      <Icon name="account" size={24} color="#374151" />
                      <Text style={styles.driverName}>{driver.name}</Text>

                      <View style={[
                        styles.trackingBadge,
                        { backgroundColor: driver.sedang_berjalan ? '#dcfce7' : '#f3f4f6' }
                      ]}>
                        <Icon
                          name={driver.sedang_berjalan ? 'truck-delivery' : 'pause-circle'}
                          size={12}
                          color={driver.sedang_berjalan ? '#16a34a' : '#6b7280'}
                        />
                        <Text style={[
                          styles.trackingText,
                          { color: driver.sedang_berjalan ? '#16a34a' : '#6b7280' }
                        ]}>
                          {driver.sedang_berjalan ? 'Sedang Berjalan' : 'Tidak Berjalan'}
                        </Text>
                      </View>
                    </View>

                    {/* Lokasi — pakai flat property dari api.js */}
                    {driver.has_location ? (
                      <Text style={styles.locationText}>
                        📍 {parseFloat(driver.latitude).toFixed(5)}, {parseFloat(driver.longitude).toFixed(5)}{'\n'}
                        {driver.tracked_at
                          ? `Terakhir update: ${new Date(driver.tracked_at).toLocaleString('id-ID')}`
                          : 'Waktu tidak tersedia'
                        }
                      </Text>
                    ) : (
                      <Text style={styles.noLocation}>
                        Belum ada data lokasi
                        {driver.sedang_berjalan ? ' — menunggu kiriman GPS...' : ''}
                      </Text>
                    )}

                  </View>
                ))
              )}
            </View>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container:       { flex: 1, backgroundColor: '#f5f7fb' },
  title:           { fontSize: 22, fontWeight: 'bold', textAlign: 'center', marginVertical: 16 },
  subtitle:        { fontSize: 18, fontWeight: '600', marginHorizontal: 16, marginTop: 10, marginBottom: 10 },

  mapContainer:    { height: 300, marginHorizontal: 16, marginBottom: 10, borderRadius: 14, overflow: 'hidden' },
  map:             { flex: 1 },

  // Overlay di atas peta saat tidak ada marker
  mapOverlay:      { position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, justifyContent: 'center', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.6)' },
  mapOverlayText:  { marginTop: 8, color: '#6b7280', fontSize: 13, textAlign: 'center' },

  markerContainer: { alignItems: 'center' },

  driverCard:      { backgroundColor: '#fff', padding: 16, borderRadius: 14, marginBottom: 12 },
  driverHeader:    { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  driverName:      { marginLeft: 10, fontWeight: '600', fontSize: 16 },

  trackingBadge:   { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 20, marginLeft: 'auto', gap: 4 },
  trackingText:    { fontSize: 11, fontWeight: '600' },

  locationText:    { fontSize: 14, color: '#374151', lineHeight: 20 },
  noLocation:      { fontSize: 14, color: '#9ca3af', fontStyle: 'italic' },

  errorBox:        { margin: 16, padding: 16, backgroundColor: '#fee2e2', borderRadius: 12 },
  errorText:       { color: '#dc2626', textAlign: 'center' },

  emptyBox:        { alignItems: 'center', paddingVertical: 32 },
  emptyText:       { marginTop: 8, color: '#9ca3af', fontSize: 14 },

  center:          { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText:     { marginTop: 10, color: '#6b7280' },
});
