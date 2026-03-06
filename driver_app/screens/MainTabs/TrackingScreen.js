import React, { useState, useEffect } from 'react';
import { View, Text, Button, Alert, StyleSheet } from 'react-native';
import * as Location from 'expo-location'; // library untuk akses GPS HP
import { sendLocation, logout } from '../../utils/api'; // kirim lokasi ke laravel dan logout

export default function TrackingScreen({ navigation }) {
  const [isTracking, setIsTracking] = useState(false);
  const [location, setLocation] = useState(null);
  const [errorMsg, setErrorMsg] = useState(null);
  let intervalId;

  // Minta izin akses lokasi saat halaman tracking dibuka
  useEffect(() => {
    (async () => {
      const { status } = await Location.requestForegroundPermissionsAsync(); // minta izin akses GPS
      if (status !== 'granted') {
        setErrorMsg('Akses GPS ditolak. Aktifkan di settings HP.');
        return;
      }
    })();

    return () => {
      if (intervalId) clearInterval(intervalId);
    };
  }, []);

  // Mulai tracking lokasi
  const startTracking = async () => {
    setIsTracking(true);
    intervalId = setInterval(async () => {
      try {
        // Dapatkan lokasi sekarang
        const loc = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.High });
        setLocation(loc.coords); // Simpan koordinat lokasi
        await sendLocation(loc.coords.latitude, loc.coords.longitude); // Kirim ke server
        Alert.alert('Info', 'Lokasi terkirim!');
      } catch (error) {
        Alert.alert('Error', 'Gagal ambil/kirim lokasi.');
        console.error(error);
      }
    }, 5000);  // Kirim setiap 5 detik
  };

  const stopTracking = () => {
    setIsTracking(false);
    if (intervalId) clearInterval(intervalId);
    setLocation(null);
  };

  const handleLogout = async () => {
    try {
      await logout();
      navigation.navigate('Login');
    } catch (error) {
      Alert.alert('Error', 'Logout gagal.');
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Tracking GPS Driver</Text>
      {errorMsg && <Text style={styles.error}>{errorMsg}</Text>}
      {location && (
        <Text style={styles.info}>
          Lokasi sekarang: Lat {location.latitude.toFixed(4)}, Long {location.longitude.toFixed(4)}
        </Text>
      )}
      {!isTracking ? (
        <Button title="Mulai Tracking GPS" onPress={startTracking} color="#28a745" />
      ) : (
        <Button title="Stop Tracking" onPress={stopTracking} color="#dc3545" />
      )}
      <Button title="Logout" onPress={handleLogout} color="#6c757d" style={styles.logoutButton} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
    backgroundColor: '#f8f9fa',
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    marginBottom: 30,
    textAlign: 'center',
  },
  info: {
    fontSize: 16,
    textAlign: 'center',
    marginBottom: 20,
  },
  error: {
    fontSize: 16,
    color: 'red',
    textAlign: 'center',
    marginBottom: 20,
  },
  logoutButton: {
    marginTop: 20,
  },
});