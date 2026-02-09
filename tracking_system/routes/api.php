<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
