import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';

import AslapDashboardScreen from './AslapDashboardScreen';
import AslapMonitoringScreen from './AslapMonitoringScreen';
import AslapDistribusiScreen from './AslapDistribusiScreen';
import ProfilScreen from '../MainTabs/ProfilScreen';

const Tab = createBottomTabNavigator();

export default function AslapTabs() {
  return (
    <Tab.Navigator
      initialRouteName="Dashboard"
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: '#0d6efd',
        tabBarInactiveTintColor: 'gray',
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