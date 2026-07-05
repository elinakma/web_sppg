<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Models\LocationHistory;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;

class DriverLocationController extends Controller
{
    public function __construct(protected TrackingService $trackingService)
    {}
 
    public function allLocations(): JsonResponse
    {
        $drivers = User::where('role', 'Driver')
            ->where('status', 'Aktif')
            ->with(['location' => fn($q) => $q->latest()->limit(1)])
            ->get()
            ->map(function ($driver) {
                $loc = $driver->location;

                $isOnline = false;

                if ($loc) {
                    $isOnline = now()->diffInSeconds($loc->tracked_at) <= 60;
                }
 
                // Hitung total jarak hari ini
                $totalKm      = $this->trackingService->getTotalDistanceToday($driver->id);
                $pointCount   = LocationHistory::forDriver($driver->id)->today()->count();
 
                return [
                    'id'    => $driver->id,
                    'name'  => $driver->name,
                    'email' => $driver->email,
                    'is_online' => $isOnline,
                    'location' => $loc ? [
                        'latitude'   => (float) $loc->latitude,
                        'longitude'  => (float) $loc->longitude,
                        'created_at' => $loc->created_at,
                    ] : null,
                    'tracking_stats' => [
                        'total_km'    => $totalKm,
                        'point_count' => $pointCount,
                    ],
                ];
            });
 
        return response()->json($drivers);
    }

    public function history(int $id): JsonResponse
    {
        $driver = User::where('role', 'Driver')
            ->where('id', $id)
            ->firstOrFail();
 
        $points = LocationHistory::forDriver($driver->id)
            ->today()
            ->orderBy('tracked_at')
            ->get(['latitude', 'longitude', 'tracked_at'])
            ->map(fn($p) => [
                'latitude'   => (float) $p->latitude,
                'longitude'  => (float) $p->longitude,
                'tracked_at' => $p->tracked_at->setTimezone('Asia/Jakarta')->toDateTimeString(),
            ]);
 
        return response()->json($points);
    }
 
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'latitude'  => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $user = $request->user();

            if (!$user || !$user->isDriver()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;

            Location::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'latitude'   => $lat,
                    'longitude'  => $lng,
                    'tracked_at' => now(),
                ]
            );

            $result = $this->trackingService->processLocation($user->id, $lat, $lng);

            return response()->json([
                'success'  => true,
                'message'  => 'Lokasi berhasil diupdate',
                'tracking' => $result,
            ]);

        } catch (\Exception $e) {
            \Log::error('Track Location Error', [
                'user_id' => $request->user()?->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan lokasi'
            ], 500);
        }
    }
}
