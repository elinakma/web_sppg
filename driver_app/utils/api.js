import axios from 'axios'; // library untuk melakukan request HTTP ke server laravel
import * as SecureStore from 'expo-secure-store'; // library untuk menyimpan data secara aman di perangkat

const API_BASE_URL = 'https://dipsacaceous-dere-bridgette.ngrok-free.dev/api';

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

export const forgotPassword = async (email) => {
  try {
    const response = await axios.post(`${API_BASE_URL}/forgot-password`, { email });
    return response.data;
  } catch (error) {
    console.error('Error forgot password:', error.response?.data || error.message);
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


// Ambil daftar sekolah yang diassign ke driver ini
export const getDistribusi = async () => {
  try {
    const token = await SecureStore.getItemAsync('authToken');
    console.log('Token:', token); // Cek token ada gak
    if (!token) throw new Error('No token found');

    const response = await axios.get(`${API_BASE_URL}/distribusi`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    console.log('Response sekolah:', response.data); // Cek data
    return response.data.sekolah || [];
  } catch (error) {
    console.error('Error getDistribusi:', error.response?.data || error.message);
    throw error;
  }
};


// Ambil statistik dashboard driver
export const getDriverStats = async () => {
  try {
    const token = await SecureStore.getItemAsync('authToken');
    if (!token) throw new Error('No token found');

    const response = await axios.get(`${API_BASE_URL}/driver-stats`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    return response.data.stats;
  } catch (error) {
    console.error('Error getDriverStats:', error.response?.data || error.message);
    throw error;
  }
};

// Mulai perjalanan hari ini
export const startTracking = async () => {
  try {
    const token = await SecureStore.getItemAsync('authToken');
    if (!token) throw new Error('No token found');

    const response = await axios.post(`${API_BASE_URL}/start-tracking`, {}, {
      headers: { Authorization: `Bearer ${token}` },
    });

    return response.data;
  } catch (error) {
    throw error;
  }
};

// Checklist satu sekolah (tandai selesai)
export const checklistSekolah = async (idDistribusiSekolah) => {
  try {
    const token = await SecureStore.getItemAsync('authToken');
    if (!token) throw new Error('No token found');

    const response = await axios.post(`${API_BASE_URL}/checklist-sekolah`, {
      id_distribusi_sekolah: idDistribusiSekolah,
    }, {
      headers: { Authorization: `Bearer ${token}` },
    });

    return response.data;
  } catch (error) {
    throw error;
  }
};

export const stopTracking = async () => {
  try {
    const token = await SecureStore.getItemAsync('authToken');

    const response = await axios.post(`${API_BASE_URL}/stop-tracking`, {}, {
      headers: { Authorization: `Bearer ${token}` },
    });

    return response.data;
  } catch (error) {
    throw error;
  }
};