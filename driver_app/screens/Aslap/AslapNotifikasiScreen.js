import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, FlatList, TouchableOpacity, RefreshControl,
  ActivityIndicator, StyleSheet
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import {
  getAslapNotifikasiLatest,
  bacaNotifikasi,
  bacaSemuaNotifikasi,
} from '../../utils/api';

export default function AslapNotifikasiScreen() {
  const [notifikasi, setNotifikasi]   = useState([]);
  const [loading, setLoading]         = useState(true);
  const [refreshing, setRefreshing]   = useState(false);
  const [belumDibaca, setBelumDibaca] = useState(0);
  const intervalRef = useRef(null);

  useEffect(() => {
    fetchNotifikasi();
    // Polling tiap 15 detik saat screen aktif
    intervalRef.current = setInterval(() => fetchNotifikasi(true), 15_000);
    return () => clearInterval(intervalRef.current);
  }, []);

  const fetchNotifikasi = async (isBackground = false) => {
    if (!isBackground) setLoading(true);
    try {
      const data = await getAslapNotifikasiLatest();
      setNotifikasi(data.notifikasi || []);
      setBelumDibaca(data.belum_dibaca || 0);
    } catch (error) {
      console.error('[AslapNotif] Error:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchNotifikasi(false);
  };

  const handleBaca = async (id) => {
    try {
      await bacaNotifikasi(id);
      // Update state lokal — tidak perlu re-fetch
      setNotifikasi(prev =>
        prev.map(n => n.id === id ? { ...n, dibaca: true } : n)
      );
      setBelumDibaca(prev => Math.max(0, prev - 1));
    } catch (e) {
      console.error('[AslapNotif] Gagal baca:', e);
    }
  };

  const handleBacaSemua = async () => {
    if (belumDibaca === 0) return;
    try {
      await bacaSemuaNotifikasi();
      setNotifikasi(prev => prev.map(n => ({ ...n, dibaca: true })));
      setBelumDibaca(0);
    } catch (e) {
      console.error('[AslapNotif] Gagal baca semua:', e);
    }
  };

  const getIcon = (tipe) => {
    switch (tipe) {
      case 'pengiriman_selesai': return { name: 'checkmark-circle', color: '#10b981' };
      case 'perjalanan_selesai': return { name: 'car-sport',        color: '#3b82f6' };
      default:                   return { name: 'notifications',    color: '#6b7280' };
    }
  };

  const renderItem = ({ item }) => {
    const icon   = getIcon(item.tipe);
    const dibaca = item.dibaca ?? false;

    return (
      <TouchableOpacity
        style={[styles.notifItem, !dibaca && styles.notifUnread]}
        onPress={() => !dibaca && handleBaca(item.id)}
        activeOpacity={0.75}
      >
        <View style={[styles.iconContainer, { backgroundColor: icon.color + '20' }]}>
          <Ionicons name={icon.name} size={22} color={icon.color} />
        </View>

        <View style={styles.content}>
          <Text style={[styles.judul, !dibaca && styles.judulUnread]}>
            {item.judul}
          </Text>
          <Text style={styles.pesan} numberOfLines={3}>
            {item.pesan}
          </Text>
          <Text style={styles.meta}>
            {item.pengirim ?? '-'} · {item.waktu ?? ''}
          </Text>
        </View>

        {!dibaca && <View style={styles.unreadDot} />}
      </TouchableOpacity>
    );
  };

  if (loading && notifikasi.length === 0) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#0d6efd" />
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>Notifikasi</Text>
          {belumDibaca > 0 && (
            <Text style={styles.headerSub}>{belumDibaca} belum dibaca</Text>
          )}
        </View>
        {belumDibaca > 0 && (
          <TouchableOpacity onPress={handleBacaSemua} style={styles.bacaSemua}>
            <Ionicons name="checkmark-done" size={15} color="#0d6efd" />
            <Text style={styles.bacaSemuaText}>Tandai Semua</Text>
          </TouchableOpacity>
        )}
      </View>

      <FlatList
        data={notifikasi}
        keyExtractor={(item) => item.id.toString()}
        renderItem={renderItem}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        contentContainerStyle={notifikasi.length === 0 && styles.emptyContainer}
        ListEmptyComponent={
          <View style={styles.empty}>
            <Ionicons name="notifications-off-outline" size={56} color="#d1d5db" />
            <Text style={styles.emptyText}>Belum ada notifikasi</Text>
          </View>
        }
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container:    { flex: 1, backgroundColor: '#f8fafc' },
  center:       { flex: 1, justifyContent: 'center', alignItems: 'center' },

  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
  },
  headerTitle:  { fontSize: 20, fontWeight: 'bold', color: '#111827' },
  headerSub:    { fontSize: 12, color: '#6b7280', marginTop: 2 },

  bacaSemua: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingVertical: 6,
    paddingHorizontal: 12,
    backgroundColor: '#eff6ff',
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#bfdbfe',
  },
  bacaSemuaText: { color: '#0d6efd', fontWeight: '600', fontSize: 12 },

  notifItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    padding: 14,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  notifUnread:  { backgroundColor: '#f0f9ff' },

  iconContainer: {
    width: 42,
    height: 42,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
    flexShrink: 0,
  },

  content:      { flex: 1 },
  judul:        { fontSize: 14, fontWeight: '500', color: '#374151', marginBottom: 3 },
  judulUnread:  { fontWeight: '700', color: '#111827' },
  pesan:        { fontSize: 13, color: '#4b5563', lineHeight: 19 },
  meta:         { fontSize: 11, color: '#9ca3af', marginTop: 5 },

  unreadDot: {
    width: 9,
    height: 9,
    borderRadius: 5,
    backgroundColor: '#0d6efd',
    marginTop: 5,
    flexShrink: 0,
  },

  emptyContainer: { flex: 1 },
  empty: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 100,
  },
  emptyText:    { marginTop: 12, color: '#9ca3af', fontSize: 15 },
});
