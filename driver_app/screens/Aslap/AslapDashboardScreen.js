import React, { useEffect, useState } from 'react';
import { View, Text, ScrollView, StyleSheet, Image, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as SecureStore from 'expo-secure-store';
import { getAslapPengirimanHariIni, getAslapPenugasanDriver } from '../../utils/api';

export default function AslapDashboardScreen() {
  const [userName, setUserName]       = useState('');
  const [pengiriman, setPengiriman]   = useState([]);
  const [drivers, setDrivers]         = useState([]);
  const [loading, setLoading]         = useState(true);

  useEffect(() => {
    const load = async () => {
      const name = await SecureStore.getItemAsync('userName');
      setUserName(name || 'Aslap');
      const [p, d] = await Promise.all([
        getAslapPengirimanHariIni(),
        getAslapPenugasanDriver(),
      ]);
      setPengiriman(p);
      setDrivers(d);
      setLoading(false);
    };
    load();
  }, []);

  const selesai  = pengiriman.filter(i => i.status === 'selesai').length;
  const dikirim  = pengiriman.filter(i => i.status === 'dikirim').length;
  const draf     = pengiriman.filter(i => i.status === 'draf').length;

  const stats = [
    { title: 'Total Pengiriman', value: pengiriman.length, icon: 'calendar-outline',       color: '#6366F1' },
    { title: 'Dikirim',          value: dikirim,           icon: 'time-outline',            color: '#F59E0B' },
    { title: 'Selesai',          value: selesai,           icon: 'checkmark-done-outline',  color: '#10B981' },
    { title: 'Draf',             value: draf,              icon: 'document-outline',        color: '#6b7280' },
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
      </View>

      {/* WELCOME */}
      <View style={styles.welcomeCard}>
        <Text style={styles.welcomeText}>
          Selamat datang, <Text style={{ fontWeight: 'bold' }}>{userName}</Text>
        </Text>
        <Text style={{ color: '#666', marginTop: 4 }}>
          Anda login sebagai <Text style={{ fontWeight: 'bold' }}>Asisten Lapangan</Text>
        </Text>
      </View>

      {/* STATS */}
      <View style={styles.sectionCard}>
        <Text style={styles.sectionTitle}>Ringkasan Hari Ini</Text>
        <Text style={styles.sectionSub}>Data pengiriman aktif per hari ini.</Text>
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
  header:       { paddingHorizontal: 20, paddingTop: 50, paddingBottom: 20, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  leftGroup:    { flexDirection: 'row', alignItems: 'center' },
  logo:         { width: 45, height: 45, marginRight: 10 },
  appName:      { fontSize: 16, fontWeight: '600' },
  subName:      { fontSize: 14, color: '#555' },
  profile:      { alignItems: 'center' },
  profileName:  { fontSize: 12, marginTop: 2 },
  welcomeCard:  { backgroundColor: '#fff', marginHorizontal: 20, padding: 20, borderRadius: 16, marginBottom: 20, elevation: 4 },
  welcomeText:  { fontSize: 16 },
  sectionCard:  { backgroundColor: '#fff', marginHorizontal: 20, padding: 20, borderRadius: 16, marginBottom: 20, elevation: 4 },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', marginBottom: 4 },
  sectionSub:   { color: '#666', marginBottom: 14 },
  grid:         { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
  card:         { width: '48%', backgroundColor: '#f9fafb', borderRadius: 16, padding: 16, marginBottom: 14, elevation: 2 },
  iconBox:      { width: 40, height: 40, borderRadius: 12, justifyContent: 'center', alignItems: 'center', marginBottom: 10 },
  cardValue:    { fontSize: 22, fontWeight: 'bold' },
  cardTitle:    { fontSize: 13, marginTop: 4, color: '#555' },
  driverRow:    { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f0f0f0' },
  driverName:   { fontWeight: '600', fontSize: 15 },
  driverSub:    { color: '#888', fontSize: 13 },
  badgeBlue:    { backgroundColor: '#e3f2fd', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
  badgeBlueText:{ color: '#0d6efd', fontWeight: '600' },
});