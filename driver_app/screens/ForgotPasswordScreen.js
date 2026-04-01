import React, { useState } from 'react';
import { 
  View, Text, TextInput, TouchableOpacity, 
  ImageBackground, StyleSheet, Alert 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { forgotPassword } from '../utils/api';   // kita akan buat ini

export default function ForgotPasswordScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSendResetLink = async () => {
    if (!email) {
      Alert.alert('Error', 'Masukkan email Anda!');
      return;
    }

    setLoading(true);

    try {
      await forgotPassword(email);
      Alert.alert(
        'Berhasil', 
        'Kami telah mengirimkan link reset password ke email Anda. Silakan cek inbox/spam.',
        [{ text: 'OK', onPress: () => navigation.goBack() }]
      );
    } catch (error) {
      Alert.alert('Gagal', 'Email tidak ditemukan atau terjadi kesalahan.');
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <ImageBackground
      source={require('../assets/bg-mbg.jpg')}
      style={styles.background}
      imageStyle={{ opacity: 0.25 }}
    >
      <View style={styles.card}>
        <Text style={styles.title}>Lupa Password</Text>
        <Text style={styles.subtitle}>
          Masukkan email Anda untuk menerima link reset password
        </Text>

        <View style={styles.inputContainer}>
          <Ionicons name="mail-outline" size={20} color="#6c757d" style={styles.icon} />
          <TextInput
            style={styles.input}
            placeholder="Masukkan Email"
            value={email}
            onChangeText={setEmail}
            keyboardType="email-address"
            autoCapitalize="none"
          />
        </View>

        <TouchableOpacity 
          style={styles.sendBtn} 
          onPress={handleSendResetLink}
          disabled={loading}
        >
          <Text style={styles.sendText}>
            {loading ? 'Mengirim...' : 'Kirim Link Reset Password'}
          </Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.backText}>← Kembali ke Login</Text>
        </TouchableOpacity>
      </View>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  card: {
    width: '90%',
    backgroundColor: '#fff',
    borderRadius: 15,
    paddingVertical: 30,
    paddingHorizontal: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 8,
    alignItems: 'center',
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 8,
    color: '#212529',
  },
  subtitle: {
    fontSize: 14,
    color: '#6c757d',
    textAlign: 'center',
    marginBottom: 25,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#dee2e6',
    borderRadius: 8,
    backgroundColor: '#f8f9fa',
    marginBottom: 20,
    width: '100%',
    paddingHorizontal: 10,
  },
  icon: { marginRight: 8 },
  input: { flex: 1, height: 48 },
  sendBtn: {
    backgroundColor: '#0d6efd',
    borderRadius: 8,
    width: '100%',
    paddingVertical: 14,
    marginBottom: 15,
  },
  sendText: {
    color: '#fff',
    textAlign: 'center',
    fontWeight: 'bold',
    fontSize: 16,
  },
  backBtn: {
    marginTop: 10,
  },
  backText: {
    color: '#0d6efd',
    fontSize: 14,
  },
});