import React from 'react';
import { View, Text, Button, StyleSheet, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { logout } from '../../utils/api';

export default function ProfilScreen({ navigation }) {
  const handleLogout = async () => {
    try {
      await logout();
      navigation.navigate('Login');
    } catch (error) {
      alert('Logout gagal');
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Image
          source={{ uri: 'https://ui-avatars.com/api/?name=Driver&background=0d6efd&color=fff&size=128' }}
          style={styles.avatar}
        />
        <Text style={styles.name}>Driver</Text>
        <Text style={styles.role}>Pengantar MBG</Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.cardTitle}>Informasi Akun</Text>
        <Text style={styles.cardText}>Email: driver@example.com</Text>
        <Text style={styles.cardText}>Status: Aktif</Text>
      </View>

      <Button title="Logout" onPress={handleLogout} color="#dc3545" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  header: {
    alignItems: 'center',
    paddingVertical: 40,
    backgroundColor: '#0d6efd',
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
    borderWidth: 4,
    borderColor: '#fff',
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginTop: 12,
  },
  role: {
    fontSize: 16,
    color: '#e9ecef',
    marginTop: 4,
  },
  card: {
    backgroundColor: '#fff',
    margin: 20,
    padding: 20,
    borderRadius: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 5,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 12,
    color: '#212529',
  },
  cardText: {
    fontSize: 16,
    color: '#495057',
    marginBottom: 8,
  },
});