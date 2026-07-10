import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import AslapDashboardScreen from './AslapDashboardScreen';
import AslapMonitoringScreen from './AslapMonitoringScreen';
import AslapDistribusiScreen from './AslapDistribusiScreen';
import AslapNotifikasiScreen from './AslapNotifikasiScreen';
import AslapDistribusiDetailScreen from './AslapDistribusiDetailScreen';
import ProfilScreen from '../MainTabs/ProfilScreen';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

function MainTabNavigator() {
  return (
    <Tab.Navigator
      initialRouteName="Dashboard"
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: '#0d6efd',
        tabBarInactiveTintColor: 'gray',
        tabBarStyle: {
          height: 70,
          paddingBottom: 25,
          paddingTop: 8,
        },
        tabBarIcon: ({ focused, color, size }) => {
          const icons = {
            Dashboard:  focused ? 'view-dashboard'       : 'view-dashboard-outline',
            Monitoring: focused ? 'map-marker-radius'    : 'map-marker-outline',
            Distribusi: focused ? 'clipboard-list'       : 'clipboard-list-outline',
            Profil:     focused ? 'account'              : 'account-outline',
          };
          return <Icon name={icons[route.name]} size={size} color={color} />;
        },
      })}
    >
      <Tab.Screen name="Dashboard"  component={AslapDashboardScreen} />
      <Tab.Screen name="Monitoring" component={AslapMonitoringScreen} />
      <Tab.Screen name="Distribusi" component={AslapDistribusiScreen} />
      <Tab.Screen name="Profil"     component={ProfilScreen} />
    </Tab.Navigator>
  );
}

// 3. Export utama sekarang mengontrol Stack global
export default function AslapTabs() {
  return (
    <Stack.Navigator>
      {/* Tab Navigator utama masuk sebagai Screen pertama */}
      <Stack.Screen 
        name="MainTabs" 
        component={MainTabNavigator} 
        options={{ headerShown: false }} 
      />

      <Stack.Screen
        name="AslapDistribusiDetail"
        component={AslapDistribusiDetailScreen}
        options={{ headerShown: false }}
      />
      
      {/* Screen Notifikasi ditaruh di sini agar bisa full screen dan punya tombol back otomatis */}
      <Stack.Screen
        name="AslapNotifikasi"
        component={AslapNotifikasiScreen}
        options={{ 
          headerShown: false,
        }}
      />
    </Stack.Navigator>
  );
}