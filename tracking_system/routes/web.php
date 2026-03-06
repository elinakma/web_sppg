<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PaguController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\DistribusiController;
use App\Http\Controllers\MapController;

// Halaman login (bisa diakses tanpa auth)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// Logout (hanya bisa diakses jika sudah login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Group untuk semua halaman yang butuh login
Route::middleware('auth')->group(function () {

    // Dashboard utama (untuk Admin)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard_admin');
        })->name('dashboard');

        // Resource routes
        Route::resource('pengguna', PenggunaController::class);

        // Sekolah routes
        Route::resource('sekolah', SekolahController::class);

        // Pagu routes
        Route::get('/pagu', [PaguController::class, 'index'])->name('pagu.index');
        Route::put('/pagu/{pagu}', [PaguController::class, 'update'])->name('pagu.update');

        // Distribusi
        Route::prefix('distribusi')->name('distribusi.')->group(function () {
            Route::get('/', [DistribusiController::class, 'index'])->name('index');
            Route::get('/tambah', [DistribusiController::class, 'create'])->name('create');
            Route::post('/', [DistribusiController::class, 'store'])->name('store');
            Route::get('/{id}/total', [DistribusiController::class, 'kelolaTotal'])->name('total');
            Route::post('/total/simpan', [DistribusiController::class, 'simpanTotal'])->name('total.simpan');
            Route::get('/{id}/detail', [DistribusiController::class, 'detail'])->name('detail');
            Route::delete('/{id}', [DistribusiController::class, 'destroy'])->name('destroy');
            
            
            // Cetak berita acara untuk semua sekolah dalam satu distribusi
            Route::get('/{distribusiId}/berita-acara', [DistribusiController::class, 'cetakBeritaAcara'])->name('berita-acara');
        });

        // Monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/map', [MapController::class, 'index'])->name('map');
            Route::post('/assign', [MapController::class, 'storePengiriman'])->name('assign.store');
            Route::delete('/assign/{pengiriman}', [MapController::class, 'destroyPengiriman'])->name('assign.destroy');
        });

        // Map / Tracking
        Route::get('/map', [MapController::class, 'index'])->name('map.index');
    });

    Route::prefix('gizi')->name('gizi.')->group(function () {
        Route::get('/dashboard', function () {
            return view('gizi.dashboard_gizi');
        })->name('dashboard');
    });

    Route::prefix('aslap')->name('aslap.')->group(function () {
        Route::get('/dashboard', function () {
            return view('aslap.dashboard_aslap');
        })->name('dashboard');
    });

    Route::prefix('akuntan')->name('akuntan.')->group(function () {
        Route::get('/dashboard', function () {
            return view('akuntan.dashboard_akuntan');
        })->name('dashboard');
    });
});