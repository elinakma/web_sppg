import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import LoginScreen from './screens/LoginScreen';
import TrackingScreen from './screens/TrackingScreen';

const Stack = createNativeStackNavigator();

export default function App() {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName="Login">
        <Stack.Screen name="Login" component={LoginScreen} options={{ title: 'Login Driver' }} />
        <Stack.Screen name="Tracking" component={TrackingScreen} options={{ title: 'Tracking GPS' }} />
      </Stack.Navigator>
    </NavigationContainer>
  );
}