import * as TaskManager from 'expo-task-manager';
import * as Location from 'expo-location';
import { sendLocation } from './api';

const LOCATION_TASK_NAME = 'background-location-task';

TaskManager.defineTask(LOCATION_TASK_NAME, async ({ data, error }) => {
  if (error) {
    console.error('[Background Task] Error:', error);
    return;
  }

  if (data && data.locations) {
    const location = data.locations[0];

    try {
      await sendLocation(location.coords.latitude, location.coords.longitude);
      console.log('[Background Task] Lokasi terkirim:', location.coords);
    } catch (err) {
      console.error('[Background Task] Gagal kirim lokasi:', err);
    }
  }
});

export default LOCATION_TASK_NAME;