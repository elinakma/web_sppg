import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, StyleSheet, RefreshControl, Alert, ActivityIndicator, ScrollView
} from 'react-native';
import MapView, { Marker } from 'react-native-maps';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { getAslapDriverLocations } from '../../utils/api';
import { SafeAreaView } from 'react-native-safe-area-context';

export default function AslapMonitoringScreen({ navigation }) {
  const [drivers, setDrivers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const intervalRef = useRef(null);   // Untuk auto refresh

  const fetchData = async (isBackground = false) => {
    if (!isBackground) {
      setLoading(true);
    }
    setErrorMsg('');

    try {
      const data = await getAslapDriverLocations();
      console.log('[AslapMonitoring] Update lokasi berhasil:', data.length, 'driver');
      setDrivers(data);
    } catch (error) {
      console.error('[AslapMonitoring] Error:', error);
      
      let message = 'Gagal memuat data monitoring';
      if (error.response?.status === 401 || error.message.includes('token')) {
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

    intervalRef.current = setInterval(() => {
      fetchData(true);
    }, 10000); // 10 detik sekali

    return () => {
      if (intervalRef.current) clearInterval(intervalRef.current);
    };
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  const getLastLocation = (driver) => {
    if (driver.locations && driver.locations.length > 0) {
      return driver.locations[0];
    }
    return null;
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

      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >

        {/* TITLE */}
        <Text style={styles.title}>
          Pemantauan Pengiriman
        </Text>

        {errorMsg ? (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMsg}</Text>
          </View>
        ) : (
          <>
            {/* MAP */}
            <View style={styles.mapContainer}>
              <MapView
                style={styles.map}
                initialRegion={{
                  latitude: -7.55,
                  longitude: 111.52,
                  latitudeDelta: 0.8,
                  longitudeDelta: 0.8,
                }}
              >
                {drivers.map(driver => {
                  const loc = getLastLocation(driver);
                  if (!loc) return null;

                  return (
                    <Marker
                      key={driver.id}
                      coordinate={{
                        latitude: parseFloat(loc.latitude),
                        longitude: parseFloat(loc.longitude),
                      }}
                      title={driver.name}
                      description={`Update: ${new Date(loc.created_at).toLocaleTimeString('id-ID')}`}
                    >
                      <View style={styles.markerContainer}>
                        <Icon name="truck-delivery" size={34} color="#6366f1" />
                      </View>
                    </Marker>
                  );
                })}
              </MapView>
            </View>

            {/* LIST TITLE */}
            <Text style={styles.subtitle}>
              Daftar Driver Aktif ({drivers.length})
            </Text>

            {/* DRIVER LIST */}
            <View style={{ paddingHorizontal: 16 }}>
              {drivers.map(driver => {
                const loc = getLastLocation(driver);
                return (
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

                    {loc ? (
                      <Text style={styles.locationText}>
                        📍 {parseFloat(loc.latitude).toFixed(5)}, {parseFloat(loc.longitude).toFixed(5)}{'\n'}
                        Terakhir update: {new Date(loc.created_at).toLocaleString('id-ID')}
                      </Text>
                    ) : (
                      <Text style={styles.noLocation}>Belum ada data lokasi</Text>
                    )}

                  </View>
                );
              })}
            </View>
          </>
        )}

      </ScrollView>

    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container:{
    flex:1,
    backgroundColor:"#f5f7fb"
  },

  title:{
    fontSize:22,
    fontWeight:"bold",
    textAlign:"center",
    marginVertical:16
  },

  subtitle:{
    fontSize:18,
    fontWeight:"600",
    marginHorizontal:16,
    marginTop:10,
    marginBottom:10
  },

  mapContainer:{
    height:300,
    marginHorizontal:16,
    marginBottom:10,
    borderRadius:14,
    overflow:"hidden"
  },

  map:{
    flex:1
  },

  driverCard:{
    backgroundColor:"#fff",
    padding:16,
    borderRadius:14,
    marginBottom:12
  },

  driverHeader:{
    flexDirection:"row",
    alignItems:"center",
    marginBottom:8
  },

  driverName:{
    marginLeft:10,
    fontWeight:"600",
    fontSize:16
  },

  locationText:{
    fontSize:14,
    color:"#374151",
    lineHeight:20
  },

  noLocation:{
    fontSize:14,
    color:"#9ca3af",
    fontStyle:"italic"
  },

  trackingBadge:{
    flexDirection:"row",
    alignItems:"center",
    paddingHorizontal:8,
    paddingVertical:4,
    borderRadius:20,
    marginLeft:"auto",
    gap:4
  },

  trackingText:{
    fontSize:11,
    fontWeight:"600"
  },

  errorBox:{
    margin:16,
    padding:16,
    backgroundColor:"#fee2e2",
    borderRadius:12
  },

  errorText:{
    color:"#dc2626",
    textAlign:"center"
  },

  center:{
    flex:1,
    justifyContent:"center",
    alignItems:"center"
  },

  loadingText:{
    marginTop:10,
    color:"#6b7280"
  }

});