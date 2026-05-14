import React, { useEffect } from 'react';
import {
  View,
  Text,
  Image,
  StyleSheet
} from 'react-native';

export default function SplashScreen({ navigation }) {

  useEffect(() => {
    const timer = setTimeout(() => {
      navigation.replace('Login');
    }, 2500);

    return () => clearTimeout(timer);
  }, []);

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
  container:{
    flex:1,
    backgroundColor:'#fff',
    justifyContent:'center',
    alignItems:'center'
  },

  logo:{
    width:180,
    height:180
  },

  title:{
    fontSize:22,
    fontWeight:'bold',
    marginTop:15,
    color:'#133b84'
  },

  copyright:{
    position:'absolute',
    bottom:35,
    fontSize:12,
    color:'#777'
  }
});