<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Location;
Use App\Models\Sekolah;
use App\Models\Pengiriman;
use App\Models\DistribusiSekolah;
use App\Models\Notifikasi;
use Carbon\Carbon;
use App\Services\TrackingService;
use App\Http\Controllers\Api\DriverLocationController;

Route::get('/drivers/locations', [DriverLocationController::class, 'allLocations'])
    ->name('api.drivers.locations');
 
// History koordinat hari ini untuk polyline per driver
Route::get('/drivers/{id}/history', [DriverLocationController::class, 'history'])
    ->name('api.drivers.history');

// Auth Mobile 
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);
 
    $user = User::where('email', $request->email)->first();
 
    if ($user && $user->status === 'Nonaktif') {
        return response()->json([
            'error' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'
        ], 403);
    }
 
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
 
        if (!in_array($user->role, ['Driver', 'Aslap'])) {
            return response()->json([
                'error' => "Akun {$user->role} hanya dapat login melalui website."
            ], 403);
        }
 
        $token = $user->createToken('mobile-token')->plainTextToken;
 
        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]
        ]);
    }
 
    return response()->json(['error' => 'Email atau password salah'], 401);
});
 
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
 
    $status = Password::sendResetLink($request->only('email'));
 
    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'success' => true,
            'message' => 'Link reset password berhasil dikirim ke email'
        ]);
    }
 
    return response()->json(['success' => false, 'message' => 'Email tidak ditemukan'], 400);
});
 
//  DRIVER AND ASLAP ROUTES (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Driver
    Route::get('/distribusi', function (Request $request) {
        try {
            $user = $request->user();
 
            if (!$user->isDriver()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
 
            $today = Carbon::today()->format('Y-m-d');
 
            $sekolah = $user->assignedSekolah()
                ->with(['distribusiSekolah' => fn($q) => $q->whereDate('tanggal_harian', $today)])
                ->get()
                ->map(function ($sekolah) use ($today) {
                    $distribusi = $sekolah->distribusiSekolah()
                        ->whereDate('tanggal_harian', $today)
                        ->first();
 
                    if (!$distribusi) return null;
 
                    return [
                        'id_sekolah'            => $sekolah->id,
                        'id_distribusi_sekolah' => $distribusi->id,
                        'nama_sekolah'          => $sekolah->nama_sekolah,
                        'pic'                   => $sekolah->pic,
                        'porsi_kecil_default'   => $sekolah->porsi_kecil_default,
                        'porsi_besar_default'   => $sekolah->porsi_besar_default,
                        'status_harian'         => $distribusi->status ?? 'draf',
                        'pagu_harian'           => $distribusi->pagu_harian_sekolah,
                    ];
                })
                ->filter()
                ->values();
 
            return response()->json(['success' => true, 'sekolah' => $sekolah]);
 
        } catch (\Exception $e) {
            \Log::error('Error di /api/distribusi', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    });
 
    // Kirim lokasi GPS dari driver
    Route::post('/track', [DriverLocationController::class, 'store'])
         ->name('api.driver.track');
 
    // Statistik pengiriman hari ini
    Route::get('/driver-stats', function (Request $request) {
        try {
            $user = $request->user();
 
            if (!$user->isDriver()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
 
            $today = now()->startOfDay();
 
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
                'stats'   => [
                    'nama_driver'      => $user->name,
                    'total_hari_ini'   => $totalHariIni,
                    'selesai_hari_ini' => $selesaiHariIni,
                    'total_sekolah'    => $totalSekolah,
                ],
            ]);
 
        } catch (\Exception $e) {
            \Log::error('Error di /api/driver-stats', [
                'message' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);
 
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    });
 
    // Mulai perjalanan
    Route::post('/start-tracking', function (Request $request) {
        $user = $request->user();
 
        if (!$user->isDriver()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
 
        $distribusi = DistribusiSekolah::where('pengirim', $user->id)
            ->whereDate('tanggal_harian', now()->startOfDay())
            ->where('status', 'draf')
            ->get();
 
        foreach ($distribusi as $item) {
            $item->update(['status' => 'dikirim', 'waktu' => now()]);
        }
 
        return response()->json([
            'success'       => true,
            'message'       => 'Perjalanan dimulai. Semua sekolah ditandai dikirim.',
            'updated_count' => $distribusi->count(),
        ]);
    });
 
    // Checklist selesai satu sekolah
    Route::post('/checklist-sekolah', function (Request $request) {
        $request->validate([
            'id_distribusi_sekolah' => 'required|exists:distribusi_sekolah,id',
        ]);
 
        $user       = $request->user();
        $distribusi = DistribusiSekolah::with('sekolah')->findOrFail($request->id_distribusi_sekolah);
 
        if ($distribusi->pengirim != $user->id) {
            return response()->json(['error' => 'Bukan tugas Anda'], 403);
        }
 
        if ($distribusi->status !== 'dikirim') {
            return response()->json(['error' => 'Status tidak valid untuk checklist'], 400);
        }
 
        $distribusi->update(['status' => 'selesai', 'waktu' => now()]);
 
        $namaSekolah = $distribusi->sekolah?->nama_sekolah ?? 'Sekolah #' . $distribusi->id_sekolah;
 
        Notifikasi::kirimKeAdmin([
            'distribusi_sekolah_id' => $distribusi->id,
            'pengirim_id'           => $user->id,
            'judul'                 => 'Pengiriman Selesai',
            'pesan'                 => "Driver {$user->name} telah menyelesaikan pengiriman ke {$namaSekolah}.",
            'tipe'                  => 'pengiriman_selesai',
            'url'                   => route('admin.distribusi.detail', $distribusi->id_distribusi ?? ''),
        ]);
 
        return response()->json(['success' => true, 'message' => 'Pengiriman ditandai selesai.']);
    });
 
    // Stop tracking / selesai semua
    Route::post('/stop-tracking', function (Request $request) {
        $user = $request->user();
 
        if (!$user->isDriver()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
 
        $selesai = DistribusiSekolah::with('sekolah')
            ->where('pengirim', $user->id)
            ->whereDate('tanggal_harian', today())
            ->where('status', 'dikirim')
            ->get();
 
        foreach ($selesai as $item) {
            $item->update(['status' => 'selesai', 'waktu' => now()]);
        }
 
        if ($selesai->isNotEmpty()) {
            $namaSekolahList = $selesai
                ->map(fn($s) => $s->sekolah?->nama_sekolah ?? '-')
                ->filter()
                ->implode(', ');
 
            Notifikasi::kirimKeAdmin([
                'pengirim_id' => $user->id,
                'judul'       => 'Perjalanan Selesai',
                'pesan'       => "Driver {$user->name} telah menyelesaikan semua pengiriman ({$selesai->count()} sekolah): {$namaSekolahList}.",
                'tipe'        => 'perjalanan_selesai',
                'url'         => null,
            ]);
        }
 
        return response()->json(['success' => true, 'message' => 'Perjalanan selesai.']);
    });
 
    // Profil
    Route::get('/profil', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'telepon' => $user->telepon,
                'role'    => $user->role,
                'status'  => $user->status,
            ]
        ]);
    });
 
    Route::put('/profil', function (Request $request) {
        $user = $request->user();
 
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'telepon'  => 'nullable|string|regex:/^[89][0-9]{8,12}$/|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
 
        $user->name    = $validated['name'];
        $user->telepon = $validated['telepon'] ?? $user->telepon;
 
        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }
 
        $user->save();
 
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'telepon' => $user->telepon,
                'role'    => $user->role,
                'status'  => $user->status,
            ]
        ]);
    });
 
    // Aslap
    Route::get('/aslap/driver-locations', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $today = now()->toDateString();
 
        $drivers = User::where('role', 'Driver')
            ->with('location')
            ->get()
            ->map(function ($driver) use ($today) {
                $sedangBerjalan = DistribusiSekolah::where('pengirim', $driver->id)
                    ->whereDate('tanggal_harian', $today)
                    ->where('status', 'dikirim')
                    ->exists();
 
                $loc = $driver->location;
 
                return [
                    'id'              => $driver->id,
                    'name'            => $driver->name,
                    'email'           => $driver->email,
                    'sedang_berjalan' => $sedangBerjalan,
                    'location'        => $loc ? [
                        'latitude'   => $loc->latitude,
                        'longitude'  => $loc->longitude,
                        'tracked_at' => $loc->tracked_at?->toISOString(),
                    ] : null,
                ];
            });
 
        return response()->json(['success' => true, 'drivers' => $drivers]);
    });
 
    Route::get('/aslap/pengiriman-hari-ini', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $data = DistribusiSekolah::whereDate('tanggal_harian', now()->toDateString())
            ->with('sekolah', 'driverPengirim')
            ->get()
            ->map(fn($item) => [
                'id'           => $item->id,
                'nama_sekolah' => $item->sekolah?->nama_sekolah ?? '-',
                'driver'       => $item->driverPengirim?->name ?? '-',
                'status'       => $item->status,
                'waktu'        => $item->waktu,
                'tanggal'      => $item->tanggal_harian,
            ]);
 
        return response()->json(['success' => true, 'data' => $data]);
    });
 
    Route::get('/aslap/penugasan-driver', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $drivers = User::where('role', 'Driver')
            ->with('assignedSekolah')
            ->get()
            ->map(fn($driver) => [
                'id'             => $driver->id,
                'name'           => $driver->name,
                'email'          => $driver->email,
                'jumlah_sekolah' => $driver->assignedSekolah->count(),
                'sekolah'        => $driver->assignedSekolah->map(fn($s) => [
                    'id'           => $s->id,
                    'nama_sekolah' => $s->nama_sekolah,
                    'pic'          => $s->pic,
                ]),
            ]);
 
        return response()->json(['success' => true, 'drivers' => $drivers]);
    });
 
    Route::get('/aslap/distribusi', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $distribusi = \App\Models\Distribusi::orderBy('tanggal_awal', 'desc')
            ->get()
            ->map(function ($item) {
                $total   = DistribusiSekolah::where('id_distribusi', $item->id)->count();
                $selesai = DistribusiSekolah::where('id_distribusi', $item->id)->where('status', 'selesai')->count();
                $dikirim = DistribusiSekolah::where('id_distribusi', $item->id)->where('status', 'dikirim')->count();
 
                if ($total == 0)                          $status = 'Draf';
                elseif ($selesai == $total)               $status = 'Selesai';
                elseif ($dikirim > 0 || $selesai > 0)    $status = 'Diproses';
                else                                      $status = 'Draf';
 
                return [
                    'id'            => $item->id,
                    'tanggal_awal'  => $item->tanggal_awal,
                    'tanggal_akhir' => $item->tanggal_akhir,
                    'status'        => $status,
                ];
            });
 
        return response()->json(['success' => true, 'distribusi' => $distribusi]);
    });

    Route::get('/aslap/distribusi/{id}/detail', function (Request $request, $id) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
    
        $distribusi = \App\Models\Distribusi::findOrFail($id);
    
        $items = DistribusiSekolah::where('id_distribusi', $id)
            ->with(['sekolah', 'driverPengirim'])
            ->orderBy('tanggal_harian')
            ->orderBy('id_sekolah')
            ->get()
            ->map(fn($item) => [
                'id'           => $item->id,
                'tanggal'      => $item->tanggal_harian,
                'nama_sekolah' => $item->sekolah?->nama_sekolah ?? '-',
                'driver'       => $item->driverPengirim?->name ?? '-',
                'status'       => $item->status,
                'waktu'        => $item->waktu,
                'porsi_kecil'  => $item->porsi_kecil_harian,
                'porsi_besar'  => $item->porsi_besar_harian,
                'total'        => ($item->porsi_kecil_harian ?? 0) + ($item->porsi_besar_harian ?? 0),
                'pagu'         => $item->pagu_harian_sekolah,
            ]);
    
        $summaryPerTanggal = $items->groupBy('tanggal')->map(fn($group) => [
            'tanggal'       => $group->first()['tanggal'],
            'total_porsi'   => $group->sum('total'),
            'selesai'       => $group->where('status', 'selesai')->count(),
            'total_sekolah' => $group->count(),
            'status'        => $group->every(fn($i) => $i['status'] === 'selesai') ? 'Selesai'
                             : ($group->contains(fn($i) => in_array($i['status'], ['dikirim', 'selesai'])) ? 'Diproses' : 'Draf'),
        ])->values();
    
        return response()->json([
            'success'             => true,
            'distribusi'          => [
                'id'            => $distribusi->id,
                'tanggal_awal'  => $distribusi->tanggal_awal,
                'tanggal_akhir' => $distribusi->tanggal_akhir,
            ],
            'items'               => $items,
            'summary_per_tanggal' => $summaryPerTanggal,
        ]);
    });

    Route::get('/aslap/drivers/locations', [DriverLocationController::class, 'allLocations'])
         ->name('api.aslap.drivers.locations');
 
    Route::get('/aslap/notifikasi/latest', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $notifikasi = Notifikasi::untukUser($user->id)
            ->with('pengirim')->latest()->limit(10)->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'judul'    => $n->judul,
                'pesan'    => $n->pesan,
                'tipe'     => $n->tipe,
                'dibaca'   => $n->dibaca,
                'waktu'    => $n->waktuRelatif(),
                'pengirim' => $n->pengirim?->name ?? '-',
            ]);
 
        $belumDibaca = Notifikasi::untukUser($user->id)->belumDibaca()->count();
 
        return response()->json([
            'success'      => true,
            'notifikasi'   => $notifikasi,
            'belum_dibaca' => $belumDibaca,
        ]);
    });
 
    Route::post('/aslap/notifikasi/baca-semua', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        Notifikasi::untukUser($user->id)->belumDibaca()->update(['dibaca' => true, 'waktu_dibaca' => now()]);
 
        return response()->json(['success' => true]);
    });
 
    Route::get('/aslap/notifikasi', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $notifikasi = Notifikasi::untukUser($user->id)->with('pengirim')->latest()->paginate(20);
 
        return response()->json([
            'success'    => true,
            'data'       => $notifikasi->map(fn($n) => [
                'id'       => $n->id,
                'judul'    => $n->judul,
                'pesan'    => $n->pesan,
                'tipe'     => $n->tipe,
                'dibaca'   => $n->dibaca,
                'waktu'    => $n->waktuRelatif(),
                'pengirim' => $n->pengirim?->name ?? '-',
            ]),
            'pagination' => [
                'current_page' => $notifikasi->currentPage(),
                'last_page'    => $notifikasi->lastPage(),
                'total'        => $notifikasi->total(),
            ],
        ]);
    });
 
    Route::post('/aslap/notifikasi/{id}/baca', function (Request $request, $id) {
        $user = $request->user();
        if ($user->role !== 'Aslap') return response()->json(['error' => 'Unauthorized'], 403);
 
        $notif = Notifikasi::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $notif->tandaiDibaca();
 
        return response()->json(['success' => true]);
    });
});