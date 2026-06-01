<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sekolah;
use App\Models\Pengiriman;
use App\Models\DistribusiSekolah;

class MapController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // ambil driver aktif
        $drivers = User::where('role', 'Driver')
                    ->where('status', 'Aktif')
                    ->with(['location' => function ($query) {
                        $query->latest()->limit(1);
                    }])
                    ->with('pengiriman')
                    ->get()
                    ->map(function ($driver) use ($today) {
                        $sedangBerjalan = DistribusiSekolah::where('pengirim', $driver->id)
                            ->whereDate('tanggal_harian', $today)
                            ->where('status', 'dikirim')
                            ->exists();

                        $driver->sedang_berjalan = $sedangBerjalan;
                        return $driver;
                    });

        $sekolahSemua = Sekolah::withTrashed()
                    ->orderBy('status', 'desc')
                    ->orderBy('nama_sekolah')
                    ->get();

        $assignedSekolah = Pengiriman::whereIn('driver_id', $drivers->pluck('id'))
                                ->get()
                                ->groupBy('driver_id')
                                ->map(fn($items) => $items->pluck('sekolah_id')->toArray())
                                ->toArray();
        
        $allAssignedSekolah = Pengiriman::whereIn('driver_id', $drivers->pluck('id'))
                            ->pluck('sekolah_id')
                            ->toArray();

        $this->sinkronisasiPengirimHariIni();

        return view('admin.monitoring.map', compact('drivers', 'sekolahSemua', 'assignedSekolah', 'allAssignedSekolah'));
    }

    // Method baru: sinkronisasi pengirim ke distribusi hari ini
    private function sinkronisasiPengirimHariIni()
    {
        $today = now()->toDateString();

        $assignments = Pengiriman::all();

        foreach ($assignments as $assignment) {
            DistribusiSekolah::where('id_sekolah', $assignment->sekolah_id)
                ->whereDate('tanggal_harian', $today)
                ->whereNull('pengirim') // hanya isi jika belum ada pengirim
                ->update([
                    'pengirim' => $assignment->driver_id,
                    'status'   => 'draf',
                ]);
        }
    }

    public function releaseDriverAssignments($driverId)
    {
        // 1. Hapus dari tabel pengiriman
        Pengiriman::where('driver_id', $driverId)->delete();

        // 2. Kosongkan pengirim di distribusi_sekolah yang masih DRAF
        DistribusiSekolah::where('pengirim', $driverId)
            ->where('status', 'draf')
            ->update([
                'pengirim' => null,
                'status'   => 'draf',
            ]);
    }

    public function storePengiriman(Request $request)
    {
        $request->validate([
            'driver_id'     => 'required|exists:users,id',
            'sekolah_ids'   => 'required|array',
            'sekolah_ids.*' => 'exists:sekolah,id',
        ]);

        $driverId   = $request->driver_id;
        $sekolahIds = $request->sekolah_ids;

        // Cek konflik HANYA dengan driver lain yang statusnya masih AKTIF
        $conflicts = Pengiriman::whereIn('sekolah_id', $sekolahIds)
                        ->where('driver_id', '!=', $driverId)
                        ->whereHas('driver', function($query) {
                            $query->where('status', 'Aktif');
                        })
                        ->exists();

        if ($conflicts) {
            return back()->withErrors(['sekolah_ids' => 'Beberapa sekolah sudah diassign ke driver aktif lain.']);
        }
        
        // Ambil list sekolah lama milik driver ini sebelum dihapus (untuk update distribusi nanti)
        $oldSekolahIds = Pengiriman::where('driver_id', $driverId)->pluck('sekolah_id')->toArray();

        // Hapus assignment lama milik driver ini saja
        Pengiriman::where('driver_id', $driverId)->delete();

        foreach ($sekolahIds as $sekolahId) {
            Pengiriman::create([
                'driver_id'  => $driverId,
                'sekolah_id' => $sekolahId,
            ]);
        }

        // Update ke distribusi_sekolah hari ini
        $today = now()->toDateString();
        
        // 1. Set pengirim baru untuk sekolah yang dicentang
        DistribusiSekolah::whereIn('id_sekolah', $sekolahIds)
            ->whereDate('tanggal_harian', $today)
            ->where('status', 'draf')
            ->update(['pengirim' => $driverId]);

        // 2. Opsional: Kosongkan sekolah lama yang tidak dicentang lagi oleh driver ini
        $uncheckedSekolahIds = array_diff($oldSekolahIds, $sekolahIds);
        if (!empty($uncheckedSekolahIds)) {
            DistribusiSekolah::whereIn('id_sekolah', $uncheckedSekolahIds)
                ->whereDate('tanggal_harian', $today)
                ->where('status', 'draf')
                ->update(['pengirim' => null]);
        }

        return redirect()->route('admin.monitoring.map')
                        ->with('success', 'Sekolah berhasil dibagikan ke driver.');
    }

    public function destroyPengiriman(Pengiriman $pengiriman)
    {
        $pengiriman->delete();

        return redirect()->route('admin.monitoring.map')
                         ->with('success', 'Sekolah berhasil dihapus dari driver.');
    }
}