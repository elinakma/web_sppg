import React from 'react';
import { 
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Image
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export default function TrackingScreen() {

  const stats = [
    {
      title: "Pengiriman Hari Ini",
      value: 8,
      icon: "calendar-outline",
      color: "#6366F1"
    },
    {
      title: "Dalam Proses",
      value: 3,
      icon: "time-outline",
      color: "#F59E0B"
    },
    {
      title: "Selesai",
      value: 5,
      icon: "checkmark-done-outline",
      color: "#10B981"
    },
    {
      title: "Tertunda",
      value: 1,
      icon: "alert-circle-outline",
      color: "#EF4444"
    },
  ];

  return (
    <ScrollView style={styles.container}>

        {/* HEADER */}
            <View style={styles.header}>

        {/* KIRI (LOGO + JUDUL) */}
        <View style={styles.leftGroup}>
            <Image 
            source={require('../../assets/logo-sppg.png')} 
            style={styles.logo}
            resizeMode="contain"
            />
            <View style={styles.titleContainer}>
            <Text style={styles.appName}>Sistem Distribusi</Text>
            <Text style={styles.subName}>Makan Bergizi Gratis</Text>
            </View>
        </View>

        {/* KANAN (PROFILE) */}
        <View style={styles.profile}>
            <Ionicons name="person-circle-outline" size={45} color="#333" />
            <Text style={styles.profileName}>Driver</Text>
        </View>

        </View>

        {/* WELCOME CARD */}
        <View style={styles.welcomeCard}>
            <Text style={styles.welcomeText}>
            Selamat datang, <Text style={{ fontWeight: 'bold' }}>Nama Driver</Text>
            </Text>
            <Text style={styles.welcomeLogin}>
            Anda Login sebagai <Text style={{ fontWeight: 'bold' }}>Driver</Text>
            </Text>
        </View>

        {/* TINJAUAN */}
        <View style={styles.sectionCard}>
            <Text style={styles.sectionTitle}>Tinjauan Pengiriman</Text>
            <Text style={styles.sectionSub}>Pantau aktivitas pengiriman Anda hari ini.</Text>

            <View style={styles.grid}>
            {stats.map((item, index) => (
                <TouchableOpacity key={index} style={styles.card}>
                <View style={[styles.iconBox, { backgroundColor: item.color + "20" }]}>
                    <Ionicons name={item.icon} size={24} color={item.color} />
                </View>
                <Text style={styles.cardValue}>{item.value}</Text>
                <Text style={styles.cardTitle}>{item.title}</Text>
                </TouchableOpacity>
            ))}
            </View>
        </View>

    </ScrollView>
  );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: "#F4F6F9",
    },
    header: {
        paddingHorizontal: 20,
        paddingTop: 50,
        paddingBottom: 20,
        flexDirection: "row",
        justifyContent: "space-between",
        alignItems: "center"
    },

    leftGroup: {
        flexDirection: "row",
        alignItems: "center"
    },

    logo: {
        width: 45,
        height: 45,
        marginRight: 10
    },

    titleContainer: {
        justifyContent: "center"
    },

    appName: {
        fontSize: 16,
        fontWeight: "600"
    },
    subName: {
        fontSize: 14,
        color: "#555"
    },
    profile: {
        alignItems: "center"
    },
    profileName: {
        fontSize: 12,
        marginTop: 2
    },
    welcomeCard: {
        backgroundColor: "#fff",
        marginHorizontal: 20,
        padding: 20,
        borderRadius: 16,
        marginBottom: 20,
        elevation: 4
    },
    welcomeText: {
        fontSize: 16
    },
    welcomeSub: {
        marginTop: 5,
        color: "#666"
    },
    sectionCard: {
        backgroundColor: "#fff",
        marginHorizontal: 20,
        padding: 20,
        borderRadius: 16,
        marginBottom: 20,
        elevation: 4
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: "bold"
    },
    sectionSub: {
        marginBottom: 15
    },
    grid: {
        flexDirection: "row",
        flexWrap: "wrap",
        justifyContent: "space-between"
    },
    card: {
        width: "48%",
        backgroundColor: "#fff",
        borderRadius: 16,
        padding: 20,
        marginBottom: 15,
        elevation: 3
    },
    iconBox: {
        width: 40,
        height: 40,
        borderRadius: 12,
        justifyContent: "center",
        alignItems: "center",
        marginBottom: 10
    },
    cardValue: {
        fontSize: 22,
        fontWeight: "bold"
    },
    cardTitle: {
        fontSize: 13,
        marginTop: 5,
        color: "#555"
    }
});