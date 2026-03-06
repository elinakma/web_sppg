<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
Use App\Models\Sekolah;
use App\Models\Pengiriman;
use App\Models\DistribusiSekolah;
use Carbon\Carbon;

// Route login untuk driver (mobile app) dengan generate token menggunakan Laravel Sanctum
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        if ($user->isDriver()) {
            // Buat token Sanctum
            $token = $user->createToken('driver-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        }

        return response()->json(['error' => 'Hanya driver yang diizinkan login via API ini'], 403);
    }

    return response()->json(['error' => 'Email atau password salah'], 401);
})->name('api.login');

// menerima data lokasi dari driver yang sudah terautentikasi dan validasi, lalu simpan ke database
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/track', function (Request $request) {
        $user = $request->user();

        if (!$user->isDriver()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Simpan lokasi (asumsi tabel locations sudah ada)
        $user->locations()->create([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json(['success' => true, 'message' => 'Lokasi berhasil dikirim']);
    });
});

// mengambil lokasi terakhir semua driver yg nanti akan ditampilkan di peta admin
Route::get('/drivers-locations', function () {
    $drivers = User::where('role', 'Driver')
                   ->with(['locations' => function ($query) {
                       $query->latest()->limit(1);
                   }])
                   ->get();

    return response()->json($drivers);
})->name('api.drivers.locations');

Route::get('/distribusi', function (Request $request) {
    try {
        $user = $request->user();

        if (!$user->isDriver()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sekolah = $user->assignedSekolah()
                        ->with(['distribusiSekolah' => function ($q) {
                            $q->whereDate('tanggal_harian', '>=', Carbon::now()->startOfWeek())
                              ->whereDate('tanggal_harian', '<=', Carbon::now()->endOfWeek());
                        }])
                        ->get()
                        ->map(function ($sekolah) {
                            $distribusi = $sekolah->distribusiSekolah()
                                ->whereDate('tanggal_harian', today())
                                ->first();

                            return [
                                'id_sekolah' => $sekolah->id,
                                'id_distribusi_sekolah' => $distribusi?->id,
                                'nama_sekolah' => $sekolah->nama_sekolah,
                                'pic' => $sekolah->pic,
                                'porsi_kecil_default' => $sekolah->porsi_kecil_default,
                                'porsi_besar_default' => $sekolah->porsi_besar_default,
                                'status_harian' => $distribusi?->status ?? 'draf',
                                'pagu_harian' => $distribusi?->pagu_harian_sekolah,
                            ];
                        });

        return response()->json([
            'success' => true,
            'sekolah' => $sekolah,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error di /api/distribusi', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
})->middleware('auth:sanctum');


// Ambil statistik pengiriman hari ini untuk driver ini
Route::get('/driver-stats', function (Request $request) {
    try {
        $user = $request->user();

        if (!$user->isDriver()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $today = now()->startOfDay();

        // Debug: cek apakah kolom pengirim ada
        \Log::info('Driver ID: ' . $user->id);
        \Log::info('Today start: ' . $today);

        $totalHariIni = DistribusiSekolah::where('pengirim', $user->id)
                                         ->whereDate('tanggal_harian', $today)
                                         ->count();

        $selesaiHariIni = DistribusiSekolah::where('pengirim', $user->id)
                                            ->whereDate('tanggal_harian', $today)
                                            ->where('status', 'selesai')
                                            ->count();

        $totalSekolah = $user->assignedSekolah()->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'nama_driver' => $user->name,
                'total_hari_ini' => $totalHariIni,
                'selesai_hari_ini' => $selesaiHariIni,
                'total_sekolah' => $totalSekolah,
            ],
        ]);
    } catch (\Exception $e) {
        \Log::error('Error di /api/driver-stats', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'error' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth:sanctum');

// Mulai perjalanan: set semua distribusi hari ini jadi 'dikirim'
Route::post('/start-tracking', function (Request $request) {
    $user = $request->user();

    if (!$user->isDriver()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $today = now()->startOfDay();

    $distribusi = DistribusiSekolah::where('pengirim', $user->id)
                                   ->whereDate('tanggal_harian', $today)
                                   ->where('status', 'draf')
                                   ->get();

    foreach ($distribusi as $item) {
        $item->update([
            'status' => 'dikirim',
            'waktu' => now(),
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Perjalanan dimulai. Semua sekolah ditandai dikirim.',
        'updated_count' => $distribusi->count(),
    ]);
})->middleware('auth:sanctum');

// Tandai selesai satu distribusi sekolah
Route::post('/checklist-sekolah', function (Request $request) {
    $request->validate([
        'id_distribusi_sekolah' => 'required|exists:distribusi_sekolah,id',
    ]);

    $user = $request->user();
    $distribusi = DistribusiSekolah::findOrFail($request->id_distribusi_sekolah);

    if ($distribusi->pengirim != $user->id) {
        return response()->json(['error' => 'Bukan tugas Anda'], 403);
    }

    if ($distribusi->status !== 'dikirim') {
        return response()->json(['error' => 'Status tidak valid untuk checklist'], 400);
    }

    $distribusi->update([
        'status' => 'selesai',
        'waktu' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Pengiriman ditandai selesai.',
    ]);
})->middleware('auth:sanctum');

Route::post('/stop-tracking', function (Request $request) {

    $user = $request->user();

    if (!$user->isDriver()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    DistribusiSekolah::where('pengirim', $user->id)
        ->whereDate('tanggal_harian', today())
        ->where('status', 'dikirim')
        ->update([
            'status' => 'selesai',
            'waktu' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Perjalanan selesai.',
    ]);

})->middleware('auth:sanctum');