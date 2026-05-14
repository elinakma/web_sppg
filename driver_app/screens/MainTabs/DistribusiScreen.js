import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, FlatList, StyleSheet,
  TouchableOpacity, RefreshControl,
  Alert, ActivityIndicator
} from 'react-native';

import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import * as TaskManager from 'expo-task-manager';
import LOCATION_TASK_NAME from '../../utils/locationTask';

import {
  getDistribusi,
  startTracking,
  stopTracking,
  checklistSekolah,
} from '../../utils/api';

import * as Location from 'expo-location';

export default function DistribusiScreen() {

  const [sekolahList, setSekolahList] = useState([]);
  const [isTracking, setIsTracking]   = useState(false);
  const [loading, setLoading]         = useState(true);
  const [refreshing, setRefreshing]   = useState(false);

  // Fetch distribusi hari ini dari server 
  const fetchSekolah = async () => {
    try {
      const data = await getDistribusi();
      setSekolahList(data);

      // Jika ada yang masih 'dikirim' → tracking sedang aktif
      const adaYangDikirim = data.some(s => s.status_harian === 'dikirim');
      setIsTracking(adaYangDikirim);

    } catch (error) {
      console.log('Error fetch sekolah', error);
      setSekolahList([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchSekolah();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchSekolah();
  };

  // Kalkulasi progress 
  const totalSekolah = sekolahList.length || 0;
  const selesai      = sekolahList.filter(s => s.status_harian === 'selesai').length;
  const progress     = totalSekolah === 0 ? 0 : selesai / totalSekolah;

  // Semua selesai = semua status 'selesai' (tidak ada draf / dikirim)
  const semuaSelesai = totalSekolah > 0
    && sekolahList.every(s => s.status_harian === 'selesai');

  // Start Tracking 
  const handleStartTracking = async () => {
    Alert.alert(
      'Mulai Tracking?',
      'GPS akan terus berjalan meskipun aplikasi ditutup.',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Mulai',
          onPress: async () => {
            try {
              const { status } = await Location.requestForegroundPermissionsAsync();
              if (status !== 'granted') {
                Alert.alert('Izin GPS ditolak');
                return;
              }

              await Location.requestBackgroundPermissionsAsync();

              // Tandai semua distribusi hari ini → 'dikirim' di server
              await startTracking();

              await Location.startLocationUpdatesAsync(LOCATION_TASK_NAME, {
                accuracy: Location.Accuracy.High,
                timeInterval: 10000,
                distanceInterval: 20,
                showsBackgroundLocationIndicator: true,
                foregroundService: {
                  notificationTitle: 'Tracking Pengiriman',
                  notificationBody: 'Sedang mengirim lokasi...',
                },
              });

              setIsTracking(true);
              fetchSekolah();

            } catch (error) {
              console.error(error);
              Alert.alert('Gagal memulai tracking', error.message);
            }
          }
        }
      ]
    );
  };

  // Stop Tracking
  const handleStopTracking = async () => {
    Alert.alert(
      'Stop Tracking?',
      'Pengiriman hari ini akan ditandai selesai dan tombol tidak akan muncul lagi hari ini.',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Stop',
          style: 'destructive',
          onPress: async () => {
            try {
              if (await TaskManager.isTaskRegisteredAsync(LOCATION_TASK_NAME)) {
                await Location.stopLocationUpdatesAsync(LOCATION_TASK_NAME);
              }

              // Tandai semua yang masih 'dikirim' → 'selesai' di server
              await stopTracking();

              setIsTracking(false);

              // Refresh → sekarang semua status jadi 'selesai'
              // → semuaSelesai = true → tombol otomatis hilang
              fetchSekolah();

            } catch (error) {
              Alert.alert('Gagal menghentikan tracking');
            }
          }
        }
      ]
    );
  };

  // Checklist satu sekolah 
  const handleChecklist = async (item) => {
    if (!isTracking) {
      Alert.alert('Tracking belum dimulai');
      return;
    }
    if (item.status_harian === 'selesai') {
      Alert.alert('Sekolah ini sudah selesai');
      return;
    }

    Alert.alert(
      'Checklist Pengiriman',
      `Tandai ${item.nama_sekolah} selesai?`,
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Checklist',
          onPress: async () => {
            try {
              await checklistSekolah(item.id_distribusi_sekolah);
              fetchSekolah();
            } catch (error) {
              Alert.alert('Gagal update');
            }
          }
        }
      ]
    );
  };

  // Status badge
  const getStatusStyle = (status) => {
    switch (status) {
      case 'selesai':
        return { color: '#16a34a', bg: '#dcfce7', text: 'Selesai' };
      case 'dikirim':
        return { color: '#f59e0b', bg: '#fef3c7', text: 'Dalam Perjalanan' };
      default:
        return { color: '#6b7280', bg: '#f3f4f6', text: 'Draf' };
    }
  };

  // Render card sekolah 
  const renderSekolah = ({ item }) => {
    const status = getStatusStyle(item.status_harian);

    return (
      <View style={styles.card}>
        <View style={styles.headerCard}>
          <Icon name="school" size={24} color="#374151" />
          <Text style={styles.schoolName}>{item.nama_sekolah}</Text>
        </View>

        <View style={styles.row}>
          <Icon name="account" size={18} color="#6b7280" />
          <Text style={styles.info}>PIC: {item.pic || '-'}</Text>
        </View>

        <View style={styles.row}>
          <Icon name="food" size={18} color="#6b7280" />
          <Text style={styles.info}>
            {item.porsi_kecil_default || 0} kecil / {item.porsi_besar_default || 0} besar
          </Text>
        </View>

        <View style={[styles.badge, { backgroundColor: status.bg }]}>
          <Text style={{ color: status.color, fontWeight: '600' }}>
            {status.text}
          </Text>
        </View>

        {/* Tombol checklist hanya muncul saat tracking aktif & belum selesai */}
        {isTracking && item.status_harian !== 'selesai' && (
          <TouchableOpacity
            style={styles.checkButton}
            onPress={() => handleChecklist(item)}
          >
            <Icon name="check-circle" size={18} color="#fff" />
            <Text style={styles.checkText}>Tandai Selesai</Text>
          </TouchableOpacity>
        )}
      </View>
    );
  };

  const renderBottomAction = () => {
    if (semuaSelesai) {
      return (
        <View style={styles.doneBox}>
          <Icon name="check-circle" size={22} color="#16a34a" />
          <Text style={styles.doneText}>Semua pengiriman selesai hari ini</Text>
        </View>
      );
    }

    if (isTracking) {
      return (
        <TouchableOpacity style={styles.stopButton} onPress={handleStopTracking}>
          <Icon name="stop-circle" size={22} color="#fff" />
          <Text style={styles.startText}>Stop Tracking</Text>
        </TouchableOpacity>
      );
    }

    return (
      <TouchableOpacity style={styles.startButton} onPress={handleStartTracking}>
        <Icon name="play-circle" size={22} color="#fff" />
        <Text style={styles.startText}>Mulai Tracking</Text>
      </TouchableOpacity>
    );
  };

  // Loading
  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <ActivityIndicator size="large" color="#6366f1" />
      </SafeAreaView>
    );
  }

  // UI
  return (
    <SafeAreaView style={styles.container}>

      <Text style={styles.title}>Distribusi Sekolah</Text>

      {/* Progress */}
      <View style={styles.progressCard}>
        <Text style={styles.progressTitle}>Progress Pengiriman</Text>
        <Text style={styles.progressText}>{selesai} / {totalSekolah} Sekolah</Text>
        <View style={styles.progressBar}>
          <View style={[styles.progressFill, { width: `${progress * 100}%` }]} />
        </View>
      </View>

      <FlatList
        data={sekolahList}
        renderItem={renderSekolah}
        keyExtractor={(item, index) =>
          item.id_distribusi_sekolah
            ? item.id_distribusi_sekolah.toString()
            : `sekolah-${item.id_sekolah}-${index}`
        }
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        contentContainerStyle={{ padding: 16, paddingBottom: 120 }}
      />

      {renderBottomAction()}

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

  progressCard:{
    backgroundColor:"#fff",
    marginHorizontal:16,
    padding:16,
    borderRadius:12,
    marginBottom:10
  },

  progressTitle:{
    fontWeight:"600"
  },

  progressText:{
    marginVertical:6
  },

  progressBar:{
    height:10,
    backgroundColor:"#e5e7eb",
    borderRadius:10,
    overflow:"hidden"
  },

  progressFill:{
    height:"100%",
    backgroundColor:"#22c55e"
  },

  card:{
    backgroundColor:"#fff",
    padding:16,
    borderRadius:14,
    marginBottom:16
  },

  headerCard:{
    flexDirection:"row",
    alignItems:"center",
    marginBottom:10
  },

  schoolName:{
    marginLeft:10,
    fontWeight:"600",
    fontSize:16
  },

  row:{
    flexDirection:"row",
    alignItems:"center",
    marginBottom:6
  },

  info:{
    marginLeft:6
  },

  badge:{
    marginTop:8,
    paddingHorizontal:12,
    paddingVertical:6,
    borderRadius:20,
    alignSelf:"flex-start"
  },

  checkButton:{
    marginTop:12,
    backgroundColor:"#16a34a",
    padding:10,
    borderRadius:8,
    flexDirection:"row",
    justifyContent:"center",
    alignItems:"center"
  },

  checkText:{
    color:"#fff",
    marginLeft:6,
    fontWeight:"600"
  },

  startButton:{
    position:"absolute",
    bottom:30,
    left:20,
    right:20,
    backgroundColor:"#6366f1",
    padding:16,
    borderRadius:12,
    flexDirection:"row",
    justifyContent:"center",
    alignItems:"center"
  },

  stopButton:{
    position:"absolute",
    bottom:30,
    left:20,
    right:20,
    backgroundColor:"#ef4444",
    padding:16,
    borderRadius:12,
    flexDirection:"row",
    justifyContent:"center",
    alignItems:"center"
  },

  startText:{
    color:"#fff",
    fontWeight:"bold",
    marginLeft:8
  },

  doneBox: {
    position: "absolute",
    bottom: 30,
    left: 20,
    right: 20,
    backgroundColor: "#dcfce7",
    padding: 16,
    borderRadius: 12,
    flexDirection: "row",
    justifyContent: "center",
    alignItems: "center",
    gap: 8,
  },

  doneText: {
    color: "#16a34a",
    fontWeight: "bold",
    fontSize: 15,
  },

});