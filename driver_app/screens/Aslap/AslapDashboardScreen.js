import React, { useState, useEffect, useRef } from 'react';
import { View, Text, ScrollView, StyleSheet, Image, ActivityIndicator, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as SecureStore from 'expo-secure-store';
import {
  getAslapPengirimanHariIni,
  getAslapNotifikasiLatest,
} from '../../utils/api';

export default function AslapDashboardScreen({ navigation }) {
  const [userName, setUserName]     = useState('');
  const [pengiriman, setPengiriman] = useState([]);
  const [belumDibaca, setBelumDibaca] = useState(0);
  const [loading, setLoading]       = useState(true);

  const intervalRef = useRef(null);

  useEffect(() => {
    loadData();
    // Polling badge notifikasi tiap 15 detik
    intervalRef.current = setInterval(pollNotifikasi, 15_000);
    return () => clearInterval(intervalRef.current);
  }, []);

  const loadData = async () => {
    try {
      const name = await SecureStore.getItemAsync('userName') || 'Aslap';
      setUserName(name);

      const [p, n] = await Promise.all([
        getAslapPengirimanHariIni(),
        getAslapNotifikasiLatest(),
      ]);

      setPengiriman(p);
      setBelumDibaca(n.belum_dibaca || 0);
    } catch (error) {
      console.error('[Dashboard] Error:', error);
    } finally {
      setLoading(false);
    }
  };

  // Hanya update badge — tidak perlu render ulang semua
  const pollNotifikasi = async () => {
    try {
      const n = await getAslapNotifikasiLatest();
      setBelumDibaca(n.belum_dibaca || 0);
    } catch (_) {}
  };

  const selesai = pengiriman.filter(i => i.status === 'selesai').length;
  const dikirim = pengiriman.filter(i => i.status === 'dikirim').length;

  const stats = [
    { title: 'Total Pengiriman', value: pengiriman.length, icon: 'calendar-outline',      color: '#6366F1' },
    { title: 'Dalam Proses',     value: dikirim,           icon: 'time-outline',           color: '#F59E0B' },
    { title: 'Selesai',          value: selesai,           icon: 'checkmark-done-outline', color: '#10B981' },
  ];

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#0d6efd" />
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>

      {/* HEADER */}
      <View style={styles.header}>
        <View style={styles.leftGroup}>
          <Image source={require('../../assets/logo.png')} style={styles.logo} resizeMode="contain" />
          <View>
            <Text style={styles.appName}>Sistem Distribusi</Text>
            <Text style={styles.subName}>Makan Bergizi Gratis</Text>
          </View>
        </View>

        {/* Bell → navigasi ke screen notifikasi */}
        <TouchableOpacity
          style={styles.notifButton}
          onPress={() => navigation.navigate('AslapNotifikasi')}
        >
          <Ionicons name="notifications-outline" size={26} color="#374151" />
          {belumDibaca > 0 && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>
                {belumDibaca > 99 ? '99+' : belumDibaca}
              </Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      {/* WELCOME */}
      <View style={styles.welcomeCard}>
        <Text style={styles.welcomeText}>
          Selamat datang, <Text style={{ fontWeight: 'bold' }}>{userName}</Text>
        </Text>
        <Text style={{ color: '#666', marginTop: 4 }}>
          Anda login sebagai{' '}
          <Text style={{ fontWeight: 'bold' }}>Asisten Lapangan</Text>
        </Text>
      </View>

      {/* STATS */}
      <View style={styles.sectionCard}>
        <Text style={styles.sectionTitle}>Tinjauan Pengiriman Hari Ini</Text>
        <Text style={styles.sectionSub}>Pantau aktivitas pengiriman secara real-time.</Text>
        <View style={styles.grid}>
          {stats.map((item, i) => (
            <View key={i} style={styles.card}>
              <View style={[styles.iconBox, { backgroundColor: item.color + '20' }]}>
                <Ionicons name={item.icon} size={24} color={item.color} />
              </View>
              <Text style={styles.cardValue}>{item.value}</Text>
              <Text style={styles.cardTitle}>{item.title}</Text>
            </View>
          ))}
        </View>
      </View>

    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container:    { flex: 1, backgroundColor: '#F4F6F9' },
  center:       { flex: 1, justifyContent: 'center', alignItems: 'center' },

  header: {
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  leftGroup:    { flexDirection: 'row', alignItems: 'center' },
  logo:         { width: 45, height: 45, marginRight: 10 },
  appName:      { fontSize: 16, fontWeight: '600' },
  subName:      { fontSize: 14, color: '#555' },

  notifButton:  { position: 'relative', padding: 8 },
  badge: {
    position: 'absolute', top: 4, right: 4,
    backgroundColor: '#ef4444',
    borderRadius: 10,
    minWidth: 18, height: 18,
    justifyContent: 'center', alignItems: 'center',
    paddingHorizontal: 4,
  },
  badgeText:    { color: '#fff', fontSize: 10, fontWeight: 'bold' },

  welcomeCard: {
    backgroundColor: '#fff',
    marginHorizontal: 20, padding: 20,
    borderRadius: 16, marginBottom: 20, elevation: 4,
  },
  welcomeText:  { fontSize: 16 },

  sectionCard: {
    backgroundColor: '#fff',
    marginHorizontal: 20, padding: 20,
    borderRadius: 16, marginBottom: 20, elevation: 4,
  },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 4 },
  sectionSub:   { color: '#666', marginBottom: 14 },

  grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
  card: {
    width: '48%', backgroundColor: '#f9fafb',
    borderRadius: 16, padding: 16, marginBottom: 14, elevation: 2,
  },
  iconBox: {
    width: 40, height: 40, borderRadius: 12,
    justifyContent: 'center', alignItems: 'center', marginBottom: 10,
  },
  cardValue:    { fontSize: 22, fontWeight: 'bold' },
  cardTitle:    { fontSize: 13, marginTop: 4, color: '#555' },
});
