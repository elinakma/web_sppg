<?php

namespace App\Services;

use App\Models\LocationHistory;
use Illuminate\Support\Carbon;

class TrackingService
{
    // ─── Konstanta Filter ────────────────────────────────────────────────────
    
    /** Jarak minimum (meter) agar titik disimpan */
    const MIN_DISTANCE_METERS = 15;

    /** Jarak minimum noise — di bawah ini pasti noise GPS diam */
    const NOISE_THRESHOLD_METERS = 4;

    /** Kecepatan maksimum wajar (km/jam) — di atas ini dianggap GPS jump */
    const MAX_SPEED_KMH = 120;

    /** Sudut perubahan arah (derajat) yang dianggap "belokan signifikan" */
    const TURN_ANGLE_THRESHOLD = 30;

    /** Interval waktu maksimum (detik) — simpan meski diam (heartbeat) */
    const MAX_INTERVAL_SECONDS = 90;

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Proses titik GPS baru dari driver.
     * Urutan logika sama persis dengan pola temenmu di TrackingController:
     * 1. Noise filter
     * 2. GPS jump filter
     */
    public function processLocation(int $userId, float $latitude, float $longitude): array
    {
        $recentPoints = LocationHistory::forDriver($userId)
            ->today()
            ->orderByDesc('tracked_at')
            ->limit(3)
            ->get();

        $lastPoint = $recentPoints->first();

        // ── Titik pertama hari ini → langsung simpan ──────────────────────────
        if (!$lastPoint) {
            return $this->save($userId, $latitude, $longitude, 'first_point');
        }

        $distance       = $this->haversineDistance(
            $lastPoint->latitude, $lastPoint->longitude,
            $latitude, $longitude
        );
        $secondsElapsed = now()->diffInSeconds($lastPoint->tracked_at);

        // ── Noise filter: terlalu kecil → buang ──────────────────────────────
        if ($distance < self::NOISE_THRESHOLD_METERS) {
            return ['saved' => false, 'reason' => 'noise'];
        }

        // ── GPS jump: kecepatan tidak wajar → buang ───────────────────────────
        if ($secondsElapsed > 0) {
            $speedKmh = ($distance / 1000) / ($secondsElapsed / 3600);
            if ($speedKmh > self::MAX_SPEED_KMH) {
                return ['saved' => false, 'reason' => 'gps_jump'];
            }
        }

        // ── Simpan jika cukup jauh ATAU sudah lama 
        if ($distance >= self::MIN_DISTANCE_METERS ||
            $secondsElapsed >= self::MAX_INTERVAL_SECONDS) {
            return $this->save($userId, $latitude, $longitude, 'distance_or_interval_ok');
        }

        // ── Deteksi belokan signifikan (cek setelah distance/interval) ─────────
        if ($recentPoints->count() >= 2) {
            $prevPoint = $recentPoints->get(1);
            $turnAngle = $this->calculateTurnAngle(
                $prevPoint->latitude, $prevPoint->longitude,
                $lastPoint->latitude, $lastPoint->longitude,
                $latitude, $longitude
            );

            if ($turnAngle >= self::TURN_ANGLE_THRESHOLD) {
                return $this->save($userId, $latitude, $longitude, 'significant_turn');
            }
        }

        return ['saved' => false, 'reason' => 'below_threshold'];
    }

    /**
     * Hitung total jarak tempuh driver hari ini (km).
     */
    public function getTotalDistanceToday(int $userId): float
    {
        $points = LocationHistory::forDriver($userId)
            ->today()
            ->orderBy('tracked_at')
            ->get(['latitude', 'longitude']);

        if ($points->count() < 2) {
            return 0.0;
        }

        $total = 0.0;
        $prev  = null;

        foreach ($points as $point) {
            if ($prev) {
                $total += $this->haversineDistance(
                    $prev->latitude, $prev->longitude,
                    $point->latitude, $point->longitude
                );
            }
            $prev = $point;
        }

        return round($total / 1000, 2); // dalam km
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Simpan titik ke database.
     */
    private function save(int $userId, float $lat, float $lng, string $reason): array
    {
        LocationHistory::create([
            'user_id'             => $userId,
            'latitude'            => $lat,
            'longitude'           => $lng,
            'tracked_at'          => now(),
            'tanggal_pengiriman'  => today(),
        ]);

        return ['saved' => true, 'reason' => $reason];
    }

    /**
     * Hitung jarak dua koordinat menggunakan formula Haversine.
     * Return: meter
     */
    public function haversineDistance(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Hitung sudut belokan antara tiga titik (A → B → C).
     * Return: derajat (0–180)
     */
    private function calculateTurnAngle(
        float $aLat, float $aLon,
        float $bLat, float $bLon,
        float $cLat, float $cLon
    ): float {
        // Bearing A→B dan B→C
        $bearing1 = $this->bearing($aLat, $aLon, $bLat, $bLon);
        $bearing2 = $this->bearing($bLat, $bLon, $cLat, $cLon);

        $angle = abs($bearing2 - $bearing1);
        if ($angle > 180) {
            $angle = 360 - $angle;
        }

        return $angle;
    }

    /**
     * Hitung bearing (arah) dari titik A ke B dalam derajat (0–360).
     */
    private function bearing(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLon = deg2rad($lon2 - $lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

        return (rad2deg(atan2($y, $x)) + 360) % 360;
    }
}