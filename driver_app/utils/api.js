import axios from 'axios'; // library untuk melakukan request HTTP ke server laravel
import * as SecureStore from 'expo-secure-store'; // library untuk menyimpan data secara aman di perangkat

const API_BASE_URL = 'https://sppggeneng.my.id/api';

export const login = async (email, password) => {
  try {
    const response = await axios.post(`${API_BASE_URL}/login`,
      { email, password },
      {
        headers: {
          'Accept'      : 'application/json',
          'Content-Type': 'application/json',
        }
      }
    );
 
    const { token, user } = response.data;
    await SecureStore.setItemAsync('authToken', token);
    await SecureStore.setItemAsync('userRole', user.role);
 
    return response.data;
 
  } catch (error) {
    console.error('Login Error Full:', error.response?.data || error.message);
    throw error;
  }
};

// Logout dengan menghapus token dari secure store
export const logout = async () => {
  await SecureStore.deleteItemAsync('authToken');
  await SecureStore.deleteItemAsync('userRole');
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

// Tambahkan fungsi baru
export const getAslapDriversWithHistory = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');

  const response = await axios.get(`${API_BASE_URL}/aslap/drivers/locations`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'ngrok-skip-browser-warning': 'true',
    },
  });

  return response.data || [];
};

export const getDriverHistory = async (driverId) => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');

  const response = await axios.get(`${API_BASE_URL}/drivers/${driverId}/history`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'ngrok-skip-browser-warning': 'true',
    },
  });

  return response.data || [];
};

// ============ DRIVER API ============
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


// ============ ASLAP API ============

export const getAslapDriverLocations = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');
 
  const response = await axios.get(`${API_BASE_URL}/aslap/driver-locations`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'ngrok-skip-browser-warning': 'true',
    },
  });
 
  const drivers = response.data.drivers || [];
 
  return drivers.map(driver => ({
    id:               driver.id,
    name:             driver.name,
    email:            driver.email,
    sedang_berjalan:  driver.sedang_berjalan,
    latitude:   driver.location?.latitude  ?? null,
    longitude:  driver.location?.longitude ?? null,
    tracked_at: driver.location?.tracked_at ?? null,
    has_location: driver.location !== null,
  }));
};

export const getAslapPengirimanHariIni = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');
 
  const response = await axios.get(`${API_BASE_URL}/aslap/pengiriman-hari-ini`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'ngrok-skip-browser-warning': 'true',
    },
  });
 
  return response.data.data || [];
};

export const getAslapPenugasanDriver = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');
 
  const response = await axios.get(`${API_BASE_URL}/aslap/penugasan-driver`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'ngrok-skip-browser-warning': 'true',
    },
  });
 
  return response.data.drivers || [];
};

export const getAslapDistribusi = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');
 
  const response = await axios.get(`${API_BASE_URL}/aslap/distribusi`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'ngrok-skip-browser-warning': 'true',
    },
  });
 
  return response.data.distribusi || [];
};

export const getAslapNotifikasiLatest = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');

  const response = await axios.get(`${API_BASE_URL}/aslap/notifikasi/latest`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return response.data;
};

// Notifikasi Aslap
export const getAslapNotifikasi = async (page = 1) => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');

  const response = await axios.get(`${API_BASE_URL}/aslap/notifikasi?page=${page}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return response.data;
};

export const bacaNotifikasi = async (id) => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');

  const response = await axios.post(`${API_BASE_URL}/aslap/notifikasi/${id}/baca`, {}, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return response.data;
};

export const bacaSemuaNotifikasi = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');

  const response = await axios.post(`${API_BASE_URL}/aslap/notifikasi/baca-semua`, {}, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return response.data;
};


// ============ PROFIL PENGGUNA ============

export const getProfil = async () => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');
  const response = await axios.get(`${API_BASE_URL}/profil`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return response.data.user;
};

// Update profil user
export const updateProfil = async (data) => {
  const token = await SecureStore.getItemAsync('authToken');
  if (!token) throw new Error('No token found');
  const response = await axios.put(`${API_BASE_URL}/profil`, data, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return response.data;
};