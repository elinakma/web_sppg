import React, { useState, useEffect } from 'react';
import {
  View, Text, StyleSheet, ScrollView,
  TouchableOpacity, TextInput, Alert,
  ActivityIndicator, KeyboardAvoidingView, Platform
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import * as SecureStore from 'expo-secure-store';
import { getProfil, updateProfil, logout } from '../../utils/api';

export default function ProfilScreen({ navigation }) {
  const [user, setUser]           = useState(null);
  const [loading, setLoading]     = useState(true);
  const [saving, setSaving]       = useState(false);
  const [editMode, setEditMode]   = useState(false);

  // Form fields
  const [name, setName]                         = useState('');
  const [telepon, setTelepon]                   = useState('');
  const [password, setPassword]                 = useState('');
  const [passwordConfirm, setPasswordConfirm]   = useState('');
  const [showPass, setShowPass]                 = useState(false);

  useEffect(() => {
    loadProfil();
  }, []);

  const loadProfil = async () => {
    try {
      const data = await getProfil();
      setUser(data);
      setName(data.name);
      setTelepon(data.telepon ?? '');
    } catch (e) {
      Alert.alert('Gagal', 'Tidak dapat memuat profil.');
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    if (!name.trim()) {
      Alert.alert('Validasi', 'Nama tidak boleh kosong.');
      return;
    }
    if (password && password !== passwordConfirm) {
      Alert.alert('Validasi', 'Konfirmasi password tidak cocok.');
      return;
    }
    if (password && password.length < 8) {
      Alert.alert('Validasi', 'Password minimal 8 karakter.');
      return;
    }

    setSaving(true);
    try {
      const payload = { name, telepon };
      if (password) {
        payload.password              = password;
        payload.password_confirmation = passwordConfirm;
      }

      const result = await updateProfil(payload);
      setUser(result.user);

      // Update nama di SecureStore supaya dashboard ikut berubah
      await SecureStore.setItemAsync('userName', result.user.name);

      setEditMode(false);
      setPassword('');
      setPasswordConfirm('');
      Alert.alert('Berhasil', 'Profil berhasil diperbarui.');
    } catch (e) {
      const msg = e.response?.data?.message
        || Object.values(e.response?.data?.errors ?? {})[0]?.[0]
        || 'Gagal menyimpan profil.';
      Alert.alert('Gagal', msg);
    } finally {
      setSaving(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Konfirmasi Logout',
      'Apakah Anda yakin ingin keluar?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Keluar',
          style: 'destructive',
          onPress: async () => {
            await logout();
            navigation.replace('Login');
          },
        },
      ]
    );
  };

  const getRoleLabel = (role) => {
    const map = {
      Driver:  'Driver Pengiriman',
      Aslap:   'Asisten Lapangan',
      Admin:   'Administrator',
      Gizi:    'Ahli Gizi',
      Akuntan: 'Akuntansi',
    };
    return map[role] ?? role;
  };

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#0d6efd" />
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={{ flex: 1 }}
      >
        <ScrollView contentContainerStyle={{ paddingBottom: 40 }}>

          {/* HEADER */}
          <View style={styles.headerBg}>
            <View style={styles.avatarBox}>
              <Icon name="account-circle" size={80} color="#fff" />
            </View>
            <Text style={styles.headerName}>{user?.name}</Text>
            <View style={styles.roleBadge}>
              <Text style={styles.roleText}>{getRoleLabel(user?.role)}</Text>
            </View>
          </View>

          {/* INFO CARD */}
          <View style={styles.card}>
            <View style={styles.cardTitleRow}>
              <Icon name="information-outline" size={20} color="#0d6efd" />
              <Text style={styles.cardTitle}>Informasi Akun</Text>
            </View>

            <View style={styles.infoRow}>
              <Icon name="email-outline" size={18} color="#888" />
              <Text style={styles.infoLabel}>Email</Text>
              <Text style={styles.infoValue}>{user?.email}</Text>
            </View>

            <View style={styles.infoRow}>
              <Icon name="phone-outline" size={18} color="#888" />
              <Text style={styles.infoLabel}>Telepon</Text>
              <Text style={styles.infoValue}>
                {user?.telepon ? `+62 ${user.telepon}` : '-'}
              </Text>
            </View>

            <View style={styles.infoRow}>
              <Icon name="shield-check-outline" size={18} color="#888" />
              <Text style={styles.infoLabel}>Status</Text>
              <View style={[
                styles.statusBadge,
                { backgroundColor: user?.status === 'Aktif' ? '#dcfce7' : '#fee2e2' }
              ]}>
                <Text style={{
                  color: user?.status === 'Aktif' ? '#16a34a' : '#dc2626',
                  fontWeight: '600', fontSize: 13
                }}>
                  {user?.status}
                </Text>
              </View>
            </View>
          </View>

          {/* EDIT CARD */}
          <View style={styles.card}>
            <View style={styles.cardTitleRow}>
              <Icon name="account-edit-outline" size={20} color="#0d6efd" />
              <Text style={styles.cardTitle}>Edit Profil</Text>
              {!editMode && (
                <TouchableOpacity
                  style={styles.editBtn}
                  onPress={() => setEditMode(true)}
                >
                  <Icon name="pencil" size={15} color="#0d6efd" />
                  <Text style={styles.editBtnText}>Edit</Text>
                </TouchableOpacity>
              )}
            </View>

            {/* Nama */}
            <Text style={styles.inputLabel}>Nama</Text>
            <TextInput
              style={[styles.input, !editMode && styles.inputDisabled]}
              value={name}
              onChangeText={setName}
              editable={editMode}
              placeholder="Nama lengkap"
            />

            {/* Telepon */}
            <Text style={styles.inputLabel}>Nomor Telepon</Text>
            <View style={[styles.inputGroup, !editMode && styles.inputDisabled]}>
              <Text style={styles.inputPrefix}>+62</Text>
              <TextInput
                style={styles.inputInner}
                value={telepon}
                onChangeText={setTelepon}
                editable={editMode}
                keyboardType="numeric"
                placeholder="85xxxxxxxx"
              />
            </View>

            {editMode && (
              <>
                {/* Password */}
                <Text style={styles.inputLabel}>
                  Password Baru <Text style={styles.opsional}>(opsional)</Text>
                </Text>
                <View style={styles.inputGroup}>
                  <TextInput
                    style={styles.inputInner}
                    value={password}
                    onChangeText={setPassword}
                    secureTextEntry={!showPass}
                    placeholder="Minimal 8 karakter"
                  />
                  <TouchableOpacity onPress={() => setShowPass(!showPass)}>
                    <Icon name={showPass ? 'eye-off' : 'eye'} size={20} color="#888" />
                  </TouchableOpacity>
                </View>

                {/* Konfirmasi Password */}
                <Text style={styles.inputLabel}>Konfirmasi Password</Text>
                <View style={styles.inputGroup}>
                  <TextInput
                    style={styles.inputInner}
                    value={passwordConfirm}
                    onChangeText={setPasswordConfirm}
                    secureTextEntry={!showPass}
                    placeholder="Ulangi password baru"
                  />
                </View>

                {/* Tombol simpan & batal */}
                <View style={styles.actionRow}>
                  <TouchableOpacity
                    style={styles.cancelBtn}
                    onPress={() => {
                      setEditMode(false);
                      setName(user?.name ?? '');
                      setTelepon(user?.telepon ?? '');
                      setPassword('');
                      setPasswordConfirm('');
                    }}
                  >
                    <Text style={styles.cancelBtnText}>Batal</Text>
                  </TouchableOpacity>

                  <TouchableOpacity
                    style={styles.saveBtn}
                    onPress={handleSave}
                    disabled={saving}
                  >
                    {saving
                      ? <ActivityIndicator size="small" color="#fff" />
                      : <Text style={styles.saveBtnText}>Simpan</Text>
                    }
                  </TouchableOpacity>
                </View>
              </>
            )}
          </View>

          {/* LOGOUT */}
          <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
            <Icon name="logout" size={20} color="#fff" />
            <Text style={styles.logoutText}>Keluar</Text>
          </TouchableOpacity>

        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container:      { flex: 1, backgroundColor: '#F4F6F9' },
  center:         { flex: 1, justifyContent: 'center', alignItems: 'center' },

  // Header
  headerBg:       { backgroundColor: '#0d6efd', alignItems: 'center', paddingTop: 40, paddingBottom: 30 },
  avatarBox:      { width: 90, height: 90, borderRadius: 45, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center', marginBottom: 12 },
  headerName:     { fontSize: 22, fontWeight: 'bold', color: '#fff' },
  roleBadge:      { marginTop: 6, backgroundColor: 'rgba(255,255,255,0.2)', paddingHorizontal: 14, paddingVertical: 4, borderRadius: 20 },
  roleText:       { color: '#fff', fontSize: 13, fontWeight: '500' },

  // Card
  card:           { backgroundColor: '#fff', marginHorizontal: 16, marginTop: 16, borderRadius: 16, padding: 18, elevation: 2 },
  cardTitleRow:   { flexDirection: 'row', alignItems: 'center', marginBottom: 14 },
  cardTitle:      { fontSize: 16, fontWeight: '700', color: '#1e293b', marginLeft: 8, flex: 1 },

  // Info rows
  infoRow:        { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  infoLabel:      { fontSize: 14, color: '#888', marginLeft: 8, width: 70 },
  infoValue:      { fontSize: 14, color: '#1e293b', flex: 1 },
  statusBadge:    { paddingHorizontal: 10, paddingVertical: 3, borderRadius: 12 },

  // Edit button
  editBtn:        { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: '#eff6ff', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 8 },
  editBtnText:    { color: '#0d6efd', fontSize: 13, fontWeight: '600' },

  // Input
  inputLabel:     { fontSize: 13, color: '#64748b', marginTop: 12, marginBottom: 4, fontWeight: '500' },
  opsional:       { color: '#94a3b8', fontWeight: '400' },
  input:          { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, paddingHorizontal: 14, paddingVertical: 10, fontSize: 15, color: '#1e293b', backgroundColor: '#fff' },
  inputDisabled:  { backgroundColor: '#f8fafc', color: '#94a3b8' },
  inputGroup:     { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, paddingHorizontal: 14, paddingVertical: 10, backgroundColor: '#fff' },
  inputPrefix:    { fontSize: 15, color: '#64748b', marginRight: 6 },
  inputInner:     { flex: 1, fontSize: 15, color: '#1e293b' },

  // Action buttons
  actionRow:      { flexDirection: 'row', gap: 12, marginTop: 20 },
  cancelBtn:      { flex: 1, paddingVertical: 12, borderRadius: 10, borderWidth: 1, borderColor: '#e2e8f0', alignItems: 'center' },
  cancelBtnText:  { color: '#64748b', fontWeight: '600', fontSize: 15 },
  saveBtn:        { flex: 1, paddingVertical: 12, borderRadius: 10, backgroundColor: '#0d6efd', alignItems: 'center' },
  saveBtnText:    { color: '#fff', fontWeight: '600', fontSize: 15 },

  // Logout
  logoutBtn:      { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginHorizontal: 16, marginTop: 16, backgroundColor: '#ef4444', paddingVertical: 14, borderRadius: 14, gap: 8, elevation: 2 },
  logoutText:     { color: '#fff', fontWeight: '700', fontSize: 16 },
});