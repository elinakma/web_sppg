import React, { useState } from 'react';
import { 
  View, Text, TextInput, TouchableOpacity, 
  ImageBackground, StyleSheet, Alert 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { login } from '../utils/api';

export default function LoginScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Isi email dan password!');
      return;
    }
    try {
      await login(email, password);
      Alert.alert('Sukses', 'Login berhasil!');
      navigation.replace('Main');
    } catch (error) {
      Alert.alert('Error', 'Login gagal. Cek email/password atau server.');
      console.error(error);
    }
  };

  return (
    <ImageBackground
      source={require('../assets/bg-mbg.jpg')}
      style={styles.background}
      imageStyle={{ opacity: 0.25 }}
    >
      <View style={styles.card}>
        <Text style={styles.title}>Sistem Distribusi{'\n'}Makan Bergizi Gratis</Text>

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

        <View style={styles.inputContainer}>
          <Ionicons name="lock-closed-outline" size={20} color="#6c757d" style={styles.icon} />
          <TextInput
            style={styles.input}
            placeholder="Masukkan Kata Sandi"
            value={password}
            onChangeText={setPassword}
            secureTextEntry
          />
        </View>

        <TouchableOpacity style={styles.forgotBtn}>
          <Text style={styles.forgotText}>Lupa Password?</Text>
        </TouchableOpacity>

        <TouchableOpacity style={styles.loginBtn} onPress={handleLogin}>
          <Text style={styles.loginText}>MASUK</Text>
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
    fontSize: 20,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 25,
    color: '#212529',
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#dee2e6',
    borderRadius: 8,
    backgroundColor: '#f8f9fa',
    marginBottom: 15,
    width: '100%',
    paddingHorizontal: 10,
  },
  icon: {
    marginRight: 8,
  },
  input: {
    flex: 1,
    height: 45,
  },
  forgotBtn: {
    alignSelf: 'flex-end',
    marginBottom: 15,
  },
  forgotText: {
    color: '#0d6efd',
    fontSize: 13,
  },
  loginBtn: {
    backgroundColor: '#0d6efd',
    borderRadius: 8,
    width: '100%',
    paddingVertical: 12,
  },
  loginText: {
    color: '#fff',
    textAlign: 'center',
    fontWeight: 'bold',
    fontSize: 16,
  },
});