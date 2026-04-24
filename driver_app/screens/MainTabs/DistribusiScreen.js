import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, FlatList, StyleSheet,
  TouchableOpacity, RefreshControl,
  Alert, ActivityIndicator
} from 'react-native';

import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';

import {
  getDistribusi,
  startTracking,
  stopTracking,
  checklistSekolah,
  sendLocation
} from '../../utils/api';

import * as Location from 'expo-location';

export default function DistribusiScreen() {

  const [sekolahList, setSekolahList] = useState([]);
  const [isTracking, setIsTracking] = useState(false);

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const intervalRef = useRef(null);

  // ================= FETCH DATA =================

  const fetchSekolah = async () => {
    try {

      const data = await getDistribusi();

      console.log("Response sekolah:", data);

      setSekolahList(data);

    } catch (error) {

      console.log("Error fetch sekolah", error);
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

  // ================= PROGRESS =================

  const totalSekolah = sekolahList.length || 0;

  const selesai = sekolahList.filter(
    i => i.status_harian === "selesai"
  ).length;

  const progress = totalSekolah === 0 ? 0 : selesai / totalSekolah;

  const semuaSelesai = totalSekolah > 0 && selesai === totalSekolah;

  // ================= START TRACKING =================

  const handleStartTracking = () => {

    Alert.alert(
      "Mulai Tracking?",
      "GPS akan mulai merekam perjalanan",
      [
        { text: "Batal", style: "cancel" },
        {
          text: "Mulai",
          onPress: async () => {

            try {

              const { status } = await Location.requestForegroundPermissionsAsync();

              if (status !== "granted") {
                Alert.alert("GPS tidak diizinkan");
                return;
              }

              await startTracking();

              setIsTracking(true);

              intervalRef.current = setInterval(async () => {

                const loc = await Location.getCurrentPositionAsync({
                  accuracy: Location.Accuracy.High
                });

                await sendLocation(
                  loc.coords.latitude,
                  loc.coords.longitude
                );

              }, 5000);

              fetchSekolah();

            } catch (error) {

              Alert.alert("Gagal memulai tracking");

            }

          }
        }
      ]
    );
  };

  // ================= STOP TRACKING =================

  const handleStopTracking = () => {

    Alert.alert(
      "Selesaikan Perjalanan?",
      "Semua sekolah yang belum checklist akan otomatis selesai",
      [
        { text: "Batal", style: "cancel" },
        {
          text: "Selesai",
          onPress: async () => {

            try {

              if (intervalRef.current) {
                clearInterval(intervalRef.current);
                intervalRef.current = null;
              }

              await stopTracking();

              setIsTracking(false);

              fetchSekolah();

            } catch (error) {

              Alert.alert("Gagal menghentikan tracking");

            }

          }
        }
      ]
    );
  };

  // ================= CHECKLIST =================

  const handleChecklist = async (item) => {

    if (!isTracking) {
      Alert.alert("Tracking belum dimulai");
      return;
    }

    if (item.status_harian === "selesai") {
      Alert.alert("Sekolah ini sudah selesai");
      return;
    }

    Alert.alert(
      "Checklist Pengiriman",
      item.nama_sekolah,
      [
        { text: "Batal", style: "cancel" },
        {
          text: "Checklist",
          onPress: async () => {

            try {

              await checklistSekolah(item.id_distribusi_sekolah);

              fetchSekolah();

            } catch (error) {

              Alert.alert("Gagal update");

            }

          }
        }
      ]
    );

  };

  // ================= Button Tracking =================  
  
  {/* Sembunyikan semua tombol jika semua sekolah sudah selesai */}
  {!semuaSelesai && (
    <>
      {!isTracking ? (
        <TouchableOpacity
          style={styles.startButton}
          onPress={handleStartTracking}
        >
          <Icon name="play-circle" size={22} color="#fff" />
          <Text style={styles.startText}>Mulai Tracking</Text>
        </TouchableOpacity>

      ) : (

        <TouchableOpacity
          style={styles.stopButton}
          onPress={handleStopTracking}
        >
          <Icon name="stop-circle" size={22} color="#fff" />
          <Text style={styles.startText}>Stop Tracking</Text>
        </TouchableOpacity>

      )}
    </>
  )}

  {/* Tampilkan pesan jika semua selesai */}
  {semuaSelesai && (
    <View style={styles.doneBox}>
      <Icon name="check-circle" size={22} color="#16a34a" />
      <Text style={styles.doneText}>Semua pengiriman selesai hari ini</Text>
    </View>
  )}

  // ================= STATUS =================

  const getStatusStyle = (status) => {

    switch (status) {

      case "selesai":
        return {
          color: "#16a34a",
          bg: "#dcfce7",
          text: "Selesai"
        };

      case "dikirim":
        return {
          color: "#f59e0b",
          bg: "#fef3c7",
          text: "Dalam Perjalanan"
        };

      default:
        return {
          color: "#6b7280",
          bg: "#f3f4f6",
          text: "Draf"
        };

    }

  };

  // ================= CARD =================

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
          <Text style={styles.info}>PIC: {item.pic || "-"}</Text>
        </View>

        <View style={styles.row}>
          <Icon name="food" size={18} color="#6b7280" />
          <Text style={styles.info}>
            {item.porsi_kecil_default || 0} kecil / {item.porsi_besar_default || 0} besar
          </Text>
        </View>

        <View style={[styles.badge, { backgroundColor: status.bg }]}>
          <Text style={{ color: status.color, fontWeight: "600" }}>
            {status.text}
          </Text>
        </View>

        {isTracking && item.status_harian !== "selesai" && (

          <TouchableOpacity
            style={styles.checkButton}
            onPress={() => handleChecklist(item)}
          >

            <Icon name="check-circle" size={18} color="#fff" />

            <Text style={styles.checkText}>
              Tandai Selesai
            </Text>

          </TouchableOpacity>

        )}

      </View>

    );

  };

  // ================= LOADING =================

  if (loading) {

    return (
      <SafeAreaView style={styles.container}>
        <ActivityIndicator size="large" color="#6366f1" />
      </SafeAreaView>
    );

  }

  // ================= UI =================

  return (

    <SafeAreaView style={styles.container}>

      <Text style={styles.title}>
        Distribusi Sekolah
      </Text>

      {/* PROGRESS */}

      <View style={styles.progressCard}>

        <Text style={styles.progressTitle}>
          Progress Pengiriman
        </Text>

        <Text style={styles.progressText}>
          {selesai} / {totalSekolah} Sekolah
        </Text>

        <View style={styles.progressBar}>
          <View
            style={[
              styles.progressFill,
              { width: `${progress * 100}%` }
            ]}
          />
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

      {/* BUTTON TRACKING */}

      {!isTracking ? (

        <TouchableOpacity
          style={styles.startButton}
          onPress={handleStartTracking}
        >
          <Icon name="play-circle" size={22} color="#fff" />
          <Text style={styles.startText}>
            Mulai Tracking
          </Text>
        </TouchableOpacity>

      ) : (

        <TouchableOpacity
          style={styles.stopButton}
          onPress={handleStopTracking}
        >
          <Icon name="stop-circle" size={22} color="#fff" />
          <Text style={styles.startText}>
            Stop Tracking
          </Text>
        </TouchableOpacity>

      )}

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