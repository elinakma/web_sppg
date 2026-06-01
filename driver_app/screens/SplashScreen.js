import React, { useEffect } from 'react';
import { View, Text, Image, StyleSheet } from 'react-native';
import * as SecureStore from 'expo-secure-store';

export default function SplashScreenComponent({ navigation }) {

  useEffect(() => {
    const checkAutoLogin = async () => {
      try {
        const token    = await SecureStore.getItemAsync('authToken');
        const userRole = await SecureStore.getItemAsync('userRole');

        // Tunggu minimal 2 detik agar splash tetap kelihatan
        await new Promise(resolve => setTimeout(resolve, 2000));

        if (token && userRole) {
          if (userRole === 'Driver') {
            navigation.replace('Main');
          } else if (userRole === 'Aslap') {
            navigation.replace('AslapMain');
          } else {
            navigation.replace('Login');
          }
        } else {
          navigation.replace('Login');
        }
      } catch (_) {
        navigation.replace('Login');
      }
    };

    checkAutoLogin();
  }, [navigation]);

  return (
    <View style={styles.container}>
      <Image
        source={require('../assets/logo.png')}
        style={styles.logo}
        resizeMode="contain"
      />
      <Text style={styles.copyright}>
        © 2026 SPPG Dahlia - Geneng, Tambakromo, Ngawi
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex           : 1,
    backgroundColor: '#fff',
    justifyContent : 'center',
    alignItems     : 'center',
  },
  logo: {
    width : 180,
    height: 180,
  },
  copyright: {
    position: 'absolute',
    bottom  : 40,
    fontSize: 12,
    color   : '#777',
  },
});
