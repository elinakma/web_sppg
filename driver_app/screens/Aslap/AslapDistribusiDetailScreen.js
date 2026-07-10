import React, { useState, useEffect, useCallback } from 'react';
import {
  View, Text, StyleSheet, SectionList,
  ActivityIndicator, RefreshControl, TouchableOpacity,
} from 'react-native';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { getAslapDistribusiDetail } from '../../utils/api';

const formatTanggal = (tgl) =>
  new Date(tgl).toLocaleDateString('id-ID', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  });

const formatTanggalShort = (tgl) =>
  new Date(tgl).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'long', year: 'numeric',
  });

const formatWaktu = (waktu) => {
  if (!waktu) return '-';
  return new Date(waktu).toLocaleTimeString('id-ID', {
    hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta',
  });
};

const formatRupiah = (val) =>
  val ? `Rp ${Number(val).toLocaleString('id-ID')}` : '-';

const STATUS_CONFIG = {
  selesai:  { label: 'Selesai',  bg: '#dcfce7', color: '#16a34a', icon: 'check-circle' },
  dikirim:  { label: 'Dikirim',  bg: '#fef3c7', color: '#d97706', icon: 'truck-delivery' },
  draf:     { label: 'Draf',     bg: '#eff6ff', color: '#3b82f6', icon: 'clock-outline' },
  Selesai:  { label: 'Selesai',  bg: '#dcfce7', color: '#16a34a', icon: 'check-circle' },
  Diproses: { label: 'Diproses', bg: '#fef3c7', color: '#d97706', icon: 'truck-delivery' },
  Draf:     { label: 'Draf',     bg: '#eff6ff', color: '#3b82f6', icon: 'clock-outline' },
};

const StatusBadge = ({ status, small }) => {
  const cfg = STATUS_CONFIG[status] ?? STATUS_CONFIG.draf;
  return (
    <View style={[
      styles.badge,
      { backgroundColor: cfg.bg },
      small && { paddingHorizontal: 8, paddingVertical: 3 }
    ]}>
      <Icon name={cfg.icon} size={small ? 11 : 13} color={cfg.color} />
      <Text style={[styles.badgeText, { color: cfg.color }, small && { fontSize: 11 }]}>
        {cfg.label}
      </Text>
    </View>
  );
};

const SekolahCard = ({ item }) => (
  <View style={styles.sekolahCard}>
    <View style={styles.sekolahRow}>
      <View style={styles.sekolahInfo}>
        <View style={styles.sekolahNameRow}>
          <Icon name="school" size={15} color="#6366f1" />
          <Text style={styles.sekolahName} numberOfLines={2}>{item.nama_sekolah}</Text>
        </View>
        <View style={styles.sekolahMeta}>
          <Icon name="account-tie" size={13} color="#9ca3af" />
          <Text style={styles.sekolahMetaText}>{item.driver}</Text>
        </View>
        {item.waktu && item.status !== 'draf' && (
          <View style={styles.sekolahMeta}>
            <Icon name="clock-outline" size={13} color="#9ca3af" />
            <Text style={styles.sekolahMetaText}>{formatWaktu(item.waktu)} WIB</Text>
          </View>
        )}
      </View>
      <View style={styles.sekolahRight}>
        <StatusBadge status={item.status} small />
        <Text style={styles.porsiText}>{item.total} porsi</Text>
      </View>
    </View>
  </View>
);

const SectionHeader = ({ section }) => {
  const { tanggal, total_porsi, selesai, total_sekolah, status } = section;
  const progress = total_sekolah > 0 ? selesai / total_sekolah : 0;

  return (
    <View style={styles.sectionHeader}>
      <View style={styles.sectionHeaderTop}>
        <View>
          <Text style={styles.sectionTanggal}>{formatTanggal(tanggal)}</Text>
          <Text style={styles.sectionSub}>
            {selesai}/{total_sekolah} sekolah selesai · {total_porsi} porsi
          </Text>
        </View>
        <StatusBadge status={status} />
      </View>

      {/* Progress bar */}
      <View style={styles.progressBg}>
        <View style={[styles.progressFill, { width: `${progress * 100}%` }]} />
      </View>
    </View>
  );
};

export default function AslapDistribusiDetailScreen({ route, navigation }) {
  const { distribusiId } = route.params;

  const [data,       setData]       = useState(null);
  const [loading,    setLoading]    = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMsg,   setErrorMsg]   = useState('');
  const [activeTab,  setActiveTab]  = useState('ringkasan'); // 'ringkasan' | 'detail'

  const fetchData = useCallback(async (isRefresh = false) => {
    if (!isRefresh) setLoading(true);
    setErrorMsg('');
    try {
      const res = await getAslapDistribusiDetail(distribusiId);
      setData(res);
    } catch (e) {
      setErrorMsg(e.response?.data?.message || e.message || 'Gagal memuat data');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [distribusiId]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const onRefresh = () => { setRefreshing(true); fetchData(true); };

  // ── Siapkan sections untuk SectionList ──
  const sections = data
    ? data.summary_per_tanggal.map(summary => ({
        ...summary,
        data: data.items.filter(i => i.tanggal === summary.tanggal),
      }))
    : [];

  // ── Hitung grand total ──
  const grandTotal = data
    ? {
        totalSekolah: data.items.length,
        selesai:      data.items.filter(i => i.status === 'selesai').length,
        totalPorsi:   data.items.reduce((s, i) => s + (i.total ?? 0), 0),
        totalPagu:    data.items.reduce((s, i) => s + (Number(i.pagu) || 0), 0),
      }
    : null;

  // ── Loading ──
  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#1e3a8a" />
        <Text style={styles.loadingText}>Memuat detail distribusi...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      {/* ── Header ── */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Icon name="arrow-left" size={22} color="#fff" />
        </TouchableOpacity>
        <View style={{ flex: 1 }}>
          <Text style={styles.headerTitle}>Detail Distribusi</Text>
          {data && (
            <Text style={styles.headerSub}>
              {formatTanggalShort(data.distribusi.tanggal_awal)} s/d {formatTanggalShort(data.distribusi.tanggal_akhir)}
            </Text>
          )}
        </View>
      </View>

      {errorMsg ? (
        <View style={styles.errorBox}>
          <Icon name="alert-circle" size={18} color="#dc2626" />
          <Text style={styles.errorText}>{errorMsg}</Text>
        </View>
      ) : null}

      {data && (
        <>
          {/* ── Grand Total Card ── */}
          <View style={styles.totalCard}>
            <View style={styles.totalItem}>
              <Text style={styles.totalValue}>{grandTotal.totalSekolah}</Text>
              <Text style={styles.totalLabel}>Total Sekolah</Text>
            </View>
            <View style={styles.totalDivider} />
            <View style={styles.totalItem}>
              <Text style={[styles.totalValue, { color: '#16a34a' }]}>{grandTotal.selesai}</Text>
              <Text style={styles.totalLabel}>Selesai</Text>
            </View>
            <View style={styles.totalDivider} />
            <View style={styles.totalItem}>
              <Text style={styles.totalValue}>{grandTotal.totalPorsi}</Text>
              <Text style={styles.totalLabel}>Total Porsi</Text>
            </View>
            <View style={styles.totalDivider} />
            <View style={styles.totalItem}>
              <Text style={[styles.totalValue, { fontSize: 13, color: '#16a34a' }]}>
                {formatRupiah(grandTotal.totalPagu)}
              </Text>
              <Text style={styles.totalLabel}>Total Pagu</Text>
            </View>
          </View>

          {/* ── Tab Bar ── */}
          <View style={styles.tabBar}>
            <TouchableOpacity
              style={[styles.tab, activeTab === 'ringkasan' && styles.tabActive]}
              onPress={() => setActiveTab('ringkasan')}
            >
              <Icon name="calendar-week" size={15} color={activeTab === 'ringkasan' ? '#1e3a8a' : '#6b7280'} />
              <Text style={[styles.tabText, activeTab === 'ringkasan' && styles.tabTextActive]}>
                Ringkasan Harian
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.tab, activeTab === 'detail' && styles.tabActive]}
              onPress={() => setActiveTab('detail')}
            >
              <Icon name="format-list-bulleted" size={15} color={activeTab === 'detail' ? '#1e3a8a' : '#6b7280'} />
              <Text style={[styles.tabText, activeTab === 'detail' && styles.tabTextActive]}>
                Detail Sekolah
              </Text>
            </TouchableOpacity>
          </View>

          {/* ══════════ TAB: RINGKASAN HARIAN ══════════ */}
          {activeTab === 'ringkasan' && (
            <SectionList
              sections={sections}
              keyExtractor={(item) => String(item.id)}
              refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#1e3a8a']} />}
              contentContainerStyle={{ padding: 14, paddingBottom: 30 }}
              renderSectionHeader={({ section }) => (
                <View style={styles.ringkasanCard}>
                  <View style={styles.ringkasanTop}>
                    <View>
                      <Text style={styles.ringkasanTanggal}>{formatTanggal(section.tanggal)}</Text>
                      <Text style={styles.ringkasanSub}>
                        {section.total_sekolah} sekolah · {section.total_porsi} porsi
                      </Text>
                    </View>
                    <StatusBadge status={section.status} />
                  </View>

                  {/* Progress */}
                  <View style={styles.progressRow}>
                    <View style={styles.progressBg}>
                      <View style={[
                        styles.progressFill,
                        { width: `${section.total_sekolah > 0 ? (section.selesai / section.total_sekolah) * 100 : 0}%` }
                      ]} />
                    </View>
                    <Text style={styles.progressText}>
                      {section.selesai}/{section.total_sekolah}
                    </Text>
                  </View>

                  {/* Mini list driver */}
                  {section.data.length > 0 && (
                    <View style={styles.ringkasanDrivers}>
                      {[...new Set(section.data.map(i => i.driver).filter(d => d !== '-'))].map((driver, idx) => {
                        const driverItems = section.data.filter(i => i.driver === driver);
                        const driverSelesai = driverItems.filter(i => i.status === 'selesai').length;
                        return (
                          <View key={idx} style={styles.driverMiniRow}>
                            <Icon name="account-tie" size={13} color="#6b7280" />
                            <Text style={styles.driverMiniName}>{driver}</Text>
                            <Text style={styles.driverMiniCount}>
                              {driverSelesai}/{driverItems.length} sekolah
                            </Text>
                          </View>
                        );
                      })}
                      {section.data.filter(i => i.driver === '-').length > 0 && (
                        <View style={styles.driverMiniRow}>
                          <Icon name="account-question" size={13} color="#9ca3af" />
                          <Text style={[styles.driverMiniName, { color: '#9ca3af' }]}>
                            Belum ada driver ({section.data.filter(i => i.driver === '-').length} sekolah)
                          </Text>
                        </View>
                      )}
                    </View>
                  )}
                </View>
              )}
              renderItem={() => null} // semua render di sectionHeader
              stickySectionHeadersEnabled={false}
            />
          )}

          {/* ══════════ TAB: DETAIL SEKOLAH ══════════ */}
          {activeTab === 'detail' && (
            <SectionList
              sections={sections}
              keyExtractor={(item) => String(item.id)}
              refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#1e3a8a']} />}
              contentContainerStyle={{ padding: 14, paddingBottom: 30 }}
              renderSectionHeader={({ section }) => (
                <SectionHeader section={section} />
              )}
              renderItem={({ item }) => <SekolahCard item={item} />}
              stickySectionHeadersEnabled={false}
              ListEmptyComponent={
                <View style={styles.emptyBox}>
                  <Icon name="inbox-outline" size={36} color="#9ca3af" />
                  <Text style={styles.emptyText}>Belum ada data distribusi</Text>
                </View>
              }
            />
          )}
        </>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container:   { flex: 1, backgroundColor: '#f1f5f9' },
  center:      { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 10, color: '#6b7280', fontSize: 14 },

  // Header
  header:      { flexDirection: 'row', alignItems: 'center', backgroundColor: '#1e3a8a', paddingHorizontal: 14, paddingVertical: 14, gap: 12 },
  backBtn:     { padding: 4 },
  headerTitle: { fontSize: 16, fontWeight: '700', color: '#fff' },
  headerSub:   { fontSize: 11, color: '#93c5fd', marginTop: 2 },

  // Error
  errorBox:    { flexDirection: 'row', alignItems: 'center', gap: 8, margin: 14, padding: 14, backgroundColor: '#fee2e2', borderRadius: 12 },
  errorText:   { color: '#dc2626', flex: 1, fontSize: 13 },

  // Grand total card
  totalCard:    { flexDirection: 'row', backgroundColor: '#fff', marginHorizontal: 14, marginTop: 14, borderRadius: 14, padding: 14, elevation: 2, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 8 },
  totalItem:    { flex: 1, alignItems: 'center' },
  totalValue:   { fontSize: 18, fontWeight: '800', color: '#1e3a8a' },
  totalLabel:   { fontSize: 10, color: '#6b7280', marginTop: 3, textAlign: 'center' },
  totalDivider: { width: 1, backgroundColor: '#e5e7eb', marginVertical: 4 },

  // Tab bar
  tabBar:        { flexDirection: 'row', backgroundColor: '#fff', marginHorizontal: 14, marginTop: 12, borderRadius: 12, overflow: 'hidden', elevation: 1 },
  tab:           { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 11, gap: 6 },
  tabActive:     { borderBottomWidth: 2, borderBottomColor: '#1e3a8a' },
  tabText:       { fontSize: 13, color: '#6b7280', fontWeight: '500' },
  tabTextActive: { color: '#1e3a8a', fontWeight: '700' },

  // Badge
  badge:     { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 10, paddingVertical: 5, borderRadius: 20 },
  badgeText: { fontSize: 12, fontWeight: '700' },

  // Ringkasan card
  ringkasanCard:    { backgroundColor: '#fff', borderRadius: 14, padding: 14, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOpacity: 0.05, shadowRadius: 6 },
  ringkasanTop:     { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 10 },
  ringkasanTanggal: { fontSize: 14, fontWeight: '700', color: '#111827', marginBottom: 2 },
  ringkasanSub:     { fontSize: 12, color: '#6b7280' },
  progressRow:      { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 10 },
  progressBg:       { flex: 1, height: 6, backgroundColor: '#e5e7eb', borderRadius: 3, overflow: 'hidden' },
  progressFill:     { height: '100%', backgroundColor: '#16a34a', borderRadius: 3 },
  progressText:     { fontSize: 11, color: '#6b7280', width: 32, textAlign: 'right' },
  ringkasanDrivers: { borderTopWidth: 1, borderTopColor: '#f3f4f6', paddingTop: 8, gap: 6 },
  driverMiniRow:    { flexDirection: 'row', alignItems: 'center', gap: 6 },
  driverMiniName:   { flex: 1, fontSize: 12, color: '#111827' },
  driverMiniCount:  { fontSize: 11, color: '#6b7280' },

  // Section header (detail tab)
  sectionHeader:    { backgroundColor: '#eff6ff', borderRadius: 12, padding: 12, marginBottom: 8, borderLeftWidth: 4, borderLeftColor: '#1e3a8a' },
  sectionHeaderTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 },
  sectionTanggal:   { fontSize: 13, fontWeight: '700', color: '#1e3a8a', marginBottom: 2 },
  sectionSub:       { fontSize: 11, color: '#111827' },

  // Sekolah card
  sekolahCard:     { backgroundColor: '#fff', borderRadius: 12, padding: 12, marginBottom: 8, elevation: 1, shadowColor: '#000', shadowOpacity: 0.04, shadowRadius: 4 },
  sekolahRow:      { flexDirection: 'row', alignItems: 'flex-start' },
  sekolahInfo:     { flex: 1, gap: 5 },
  sekolahNameRow:  { flexDirection: 'row', alignItems: 'flex-start', gap: 6 },
  sekolahName:     { flex: 1, fontSize: 13, fontWeight: '600', color: '#111827' },
  sekolahMeta:     { flexDirection: 'row', alignItems: 'center', gap: 5 },
  sekolahMetaText: { fontSize: 12, color: '#111827' },
  sekolahRight:    { alignItems: 'flex-end', gap: 6, marginLeft: 8 },
  porsiText:       { fontSize: 11, color: '#111827' },

  // Empty
  emptyBox:  { alignItems: 'center', paddingVertical: 60 },
  emptyText: { marginTop: 10, color: '#9ca3af', fontSize: 14 },
});