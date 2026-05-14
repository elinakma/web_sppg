<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PaguController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\DistribusiController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MenuMakananController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\RekapController;

// Halaman login
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// Forgot Password + Reset Password
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

// Logout (hanya bisa diakses jika sudah login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])
         ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
         ->middleware('signed')
         ->name('verification.verify');

    Route::post('/email/resend', [AuthController::class, 'resendVerification'])
         ->name('verification.send');
});

// Group untuk semua halaman yang butuh login
Route::middleware('auth', 'verified')->group(function () {
    // Profile routes
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.edit');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');


    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {    
        // Dashboard admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Resource routes
        Route::resource('pengguna', PenggunaController::class);
        
        Route::patch('pengguna/{user}/status', [PenggunaController::class, 'updateStatus'])
             ->name('pengguna.status');

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

        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/cetak/{id}', [RekapController::class, 'cetakRekap'])->name('rekap.cetak');

        Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
            Route::get('/',              [NotifikasiController::class, 'index'])->name('index');
            Route::get('/dropdown',      [NotifikasiController::class, 'dropdown'])->name('dropdown');
            Route::post('/{notifikasi}/baca',  [NotifikasiController::class, 'baca'])->name('baca');
            Route::post('/baca-semua',   [NotifikasiController::class, 'bacaSemua'])->name('baca-semua');
            Route::delete('/{notifikasi}',     [NotifikasiController::class, 'destroy'])->name('destroy');
            Route::delete('/hapus-semua', [NotifikasiController::class, 'hapusSemua'])->name('hapus-semua');
        });
    });

    Route::prefix('gizi')->name('gizi.')->group(function () {
        Route::get('/dashboard', function () {
            return view('gizi.dashboard_gizi');
        })->name('dashboard');

            // ── Menu Harian ───────────────────────────────────────────────────
            Route::prefix('menu')->name('menu.')->group(function () {
                Route::get('/',          [MenuMakananController::class, 'index'])->name('index');
                Route::post('/',         [MenuMakananController::class, 'store'])->name('store');
                Route::put('/{menu}',    [MenuMakananController::class, 'update'])->name('update');
                Route::delete('/{menu}', [MenuMakananController::class, 'destroy'])->name('destroy');
            });
        
            // ── Template Menu ─────────────────────────────────────────────────
            Route::prefix('template')->name('template.')->group(function () {
                Route::get('/',          [MenuMakananController::class, 'template'])->name('index');
                Route::post('/',         [MenuMakananController::class, 'storeTemplate'])->name('store');
                Route::put('/{menu}',    [MenuMakananController::class, 'update'])->name('update');
                Route::delete('/{menu}', [MenuMakananController::class, 'destroy'])->name('destroy');
        
                // API endpoint untuk auto-fill bahan dari template (JSON)
                Route::get('/{menu}/detail', [MenuMakananController::class, 'templateDetail'])->name('detail');
            });       

            Route::prefix('akg')->name('akg.')->group(function () {
                Route::post('/store', [MenuMakananController::class, 'storeAkgHarian'])->name('store');
            });

        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/cetak/{id}', [RekapController::class, 'cetakRekap'])->name('rekap.cetak');
    });

    Route::prefix('akuntan')->name('akuntan.')->group(function () {
        Route::get('/dashboard', function () {
            return view('akuntan.dashboard_akuntan');
        })->name('dashboard');

        Route::prefix('rab')->name('rab.')->group(function () {
            Route::get('/', [RabController::class, 'index'])->name('index');
            Route::post('/harga/bulk', [RabController::class, 'updateHargaBulk'])->name('harga.bulk');
            
            Route::get('/pre-order', [RabController::class, 'preOrder'])->name('pre-order');
            Route::post('/export-pdf', [RabController::class, 'exportPdf'])->name('export-pdf');
        });

        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/cetak/{id}', [RekapController::class, 'cetakRekap'])->name('rekap.cetak');
    });
});