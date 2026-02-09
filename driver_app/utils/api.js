import axios from 'axios'; // library untuk melakukan request HTTP ke server laravel
import * as SecureStore from 'expo-secure-store'; // library untuk menyimpan data secara aman di perangkat

const API_BASE_URL = 'http://10.80.3.102:8000/api';

export const login = async (email, password) => {
  try {
    const response = await axios.post(`${API_BASE_URL}/login`, { email, password }); // request login ke server
    const { token } = response.data; // respon berupa token
    await SecureStore.setItemAsync('authToken', token);  // Simpan token
    return response.data; 
  } catch (error) {
    throw error;
  }
};

// Mengirim lokasi GPS ke server. Cek token dulu sebelum mengirim. 
export const sendLocation = async (latitude, longitude) => {
  try {
    // Ambil token dari secure store
    const token = await SecureStore.getItemAsync('authToken');
    if (!token) throw new Error('No token found');
    // Kirim lokasi ke server dengan header Authorization
    await axios.post(`${API_BASE_URL}/track`, { latitude, longitude }, {
      headers: { Authorization: `Bearer ${token}` },
    });
  } catch (error) {
    throw error;
  }
};

// Logout dengan menghapus token dari secure store
export const logout = async () => {
  await SecureStore.deleteItemAsync('authToken');
};