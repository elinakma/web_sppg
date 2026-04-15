import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, FlatList,
  ActivityIndicator, RefreshControl
} from 'react-native';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { getAslapDistribusi, getAslapPenugasanDriver } from '../../utils/api';

export default function AslapDistribusiScreen() {
  const [distribusi, setDistribusi]   = useState([]);
  const [drivers, setDrivers]         = useState([]);
  const [loading, setLoading]         = useState(true);
  const [refreshing, setRefreshing]   = useState(false);
  const [activeTab, setActiveTab]     = useState('distribusi');

  const fetchData = async () => {
    try {
      const [dist, drv] = await Promise.all([
        getAslapDistribusi(),
        getAslapPenugasanDriver(),
      ]);
      setDistribusi(dist);
      setDrivers(drv);
    } catch (e) {
      console.log('Error:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { fetchData(); }, []);

  const onRefresh = () => { setRefreshing(true); fetchData(); };

  const getStatusStyle = (status) => {
    if (status === 'Selesai')  return { color: '#16a34a', bg: '#dcfce7' };
    if (status === 'Diproses') return { color: '#f59e0b', bg: '#fef3c7' };
    return { color: '#3b82f6', bg: '#eff6ff' };
  };

  const formatTanggal = (tgl) =>
    new Date(tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#0d6efd" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Data Distribusi</Text>

      {/* TAB */}
      <View style={styles.tabRow}>
        <Text
          style={[styles.tabBtn, activeTab === 'distribusi' && styles.tabActive]}
          onPress={() => setActiveTab('distribusi')}
        >
          Distribusi
        </Text>
        <Text
          style={[styles.tabBtn, activeTab === 'penugasan' && styles.tabActive]}
          onPress={() => setActiveTab('penugasan')}
        >
          Penugasan Driver
        </Text>
      </View>

      {/* LIST DISTRIBUSI */}
      {activeTab === 'distribusi' && (
        <FlatList
          data={distribusi}
          keyExtractor={(item) => item.id.toString()}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={{ padding: 16, paddingBottom: 30 }}
          ListEmptyComponent={
            <View style={styles.empty}>
              <Icon name="inbox-outline" size={40} color="#ccc" />
              <Text style={styles.emptyText}>Belum ada data distribusi</Text>
            </View>
          }
          renderItem={({ item }) => {
            const st = getStatusStyle(item.status);
            return (
              <View style={styles.card}>
                <View style={styles.cardHeader}>
                  <Icon name="calendar-week" size={20} color="#374151" />
                  <Text style={styles.cardTitle}>Distribusi #{item.id}</Text>
                </View>
                <View style={styles.cardRow}>
                  <Icon name="calendar-start" size={15} color="#888" />
                  <Text style={styles.cardInfo}>Mulai: {formatTanggal(item.tanggal_awal)}</Text>
                </View>
                <View style={styles.cardRow}>
                  <Icon name="calendar-end" size={15} color="#888" />
                  <Text style={styles.cardInfo}>Akhir: {formatTanggal(item.tanggal_akhir)}</Text>
                </View>
                <View style={[styles.badge, { backgroundColor: st.bg }]}>
                  <Text style={{ color: st.color, fontWeight: '600', fontSize: 12 }}>
                    {item.status}
                  </Text>
                </View>
              </View>
            );
          }}
        />
      )}

      {/* PENUGASAN DRIVER */}
      {activeTab === 'penugasan' && (
        <FlatList
          data={drivers}
          keyExtractor={(item) => item.id.toString()}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={{ padding: 16, paddingBottom: 30 }}
          ListEmptyComponent={
            <View style={styles.empty}>
              <Icon name="account-off-outline" size={40} color="#ccc" />
              <Text style={styles.emptyText}>Belum ada penugasan driver</Text>
            </View>
          }
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.cardHeader}>
                <Icon name="account-tie" size={22} color="#0d6efd" />
                <Text style={styles.cardTitle}>{item.name}</Text>
              </View>
              <View style={styles.cardRow}>
                <Icon name="email-outline" size={15} color="#888" />
                <Text style={styles.cardInfo}>{item.email}</Text>
              </View>
              <Text style={styles.sekolahLabel}>Sekolah yang ditugaskan:</Text>
              {item.sekolah.length === 0 ? (
                <Text style={styles.noSekolah}>Belum ada sekolah</Text>
              ) : (
                item.sekolah.map((s, i) => (
                  <View key={i} style={styles.sekolahRow}>
                    <Icon name="school-outline" size={14} color="#6366f1" />
                    <Text style={styles.sekolahName}>{s.nama_sekolah}</Text>
                    <Text style={styles.picText}>PIC: {s.pic}</Text>
                  </View>
                ))
              )}
            </View>
          )}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container:    { flex: 1, backgroundColor: '#F4F6F9' },
  center:       { flex: 1, justifyContent: 'center', alignItems: 'center' },
  title:        { fontSize: 22, fontWeight: 'bold', textAlign: 'center', marginTop: 50, marginBottom: 12 },
  tabRow:       { flexDirection: 'row', marginHorizontal: 16, marginBottom: 12, backgroundColor: '#fff', borderRadius: 12, overflow: 'hidden', elevation: 2 },
  tabBtn:       { flex: 1, textAlign: 'center', paddingVertical: 12, fontSize: 14, color: '#888' },
  tabActive:    { color: '#0d6efd', fontWeight: '700', borderBottomWidth: 2, borderBottomColor: '#0d6efd' },
  card:         { backgroundColor: '#fff', borderRadius: 14, padding: 16, marginBottom: 14, elevation: 2 },
  cardHeader:   { flexDirection: 'row', alignItems: 'center', marginBottom: 10 },
  cardTitle:    { marginLeft: 8, fontWeight: '600', fontSize: 15, flex: 1 },
  cardRow:      { flexDirection: 'row', alignItems: 'center', marginBottom: 6 },
  cardInfo:     { marginLeft: 6, color: '#555', fontSize: 13 },
  badge:        { marginTop: 8, paddingHorizontal: 12, paddingVertical: 5, borderRadius: 20, alignSelf: 'flex-start' },
  empty:        { alignItems: 'center', marginTop: 60 },
  emptyText:    { color: '#aaa', marginTop: 10, fontSize: 15 },
  sekolahLabel: { fontSize: 13, fontWeight: '600', color: '#374151', marginTop: 8, marginBottom: 6 },
  noSekolah:    { color: '#aaa', fontSize: 13, fontStyle: 'italic' },
  sekolahRow:   { flexDirection: 'row', alignItems: 'center', backgroundColor: '#f5f5ff', borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6, marginBottom: 6 },
  sekolahName:  { marginLeft: 6, fontSize: 13, flex: 1, color: '#374151' },
  picText:      { fontSize: 11, color: '#888' },
});