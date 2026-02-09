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

        // Pagu routes
        Route::get('/pagu', [PaguController::class, 'index'])->name('pagu.index');
        Route::put('/pagu/{pagu}', [PaguController::class, 'update'])->name('pagu.update');

        // Sekolah routes
        Route::resource('sekolah', SekolahController::class);

        // Map / Tracking
        Route::get('/map', [MapController::class, 'index'])->name('map.index');
    });

    // Group khusus untuk role tertentu (bisa dikembangkan nanti dengan middleware role)
    Route::prefix('gizi')->name('gizi.')->group(function () {
        Route::get('/dashboard', function () {
            return view('gizi.dashboard_gizi'); // buat view ini nanti
        })->name('dashboard');
        // Tambahkan route lain untuk Ahli Gizi di sini nanti
    });

    Route::prefix('aslap')->name('aslap.')->group(function () {
        Route::get('/dashboard', function () {
            return view('aslap.dashboard_aslap'); // buat view ini nanti
        })->name('dashboard');
        // Tambahkan route untuk Asisten Lapangan
    });

    Route::prefix('akuntan')->name('akuntan.')->group(function () {
        Route::get('/dashboard', function () {
            return view('akuntan.dashboard_akuntan'); // buat view ini nanti
        })->name('dashboard');
        // Tambahkan route untuk Akuntansi
    });

    // Distribusi (semua role bisa akses, atau nanti bisa dibatasi)
    Route::prefix('distribusi')->name('distribusi.')->group(function () {
        Route::get('/', [DistribusiController::class, 'index'])->name('index');
        Route::get('/tambah', [DistribusiController::class, 'create'])->name('create');
        Route::post('/', [DistribusiController::class, 'store'])->name('store');
        Route::get('/{id}/total', [DistribusiController::class, 'kelolaTotal'])->name('total');
        Route::post('/total/simpan', [DistribusiController::class, 'simpanTotal'])->name('total.simpan');
        
        // Cetak berita acara untuk semua sekolah dalam satu distribusi
        Route::get('/{distribusiId}/berita-acara', [DistribusiController::class, 'cetakBeritaAcara'])
             ->name('berita-acara');
    });
});