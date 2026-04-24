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

        $drivers = User::where('role', 'Driver')
                    ->with(['locations' => function ($query) {
                        $query->latest()->limit(1);
                    }])
                    ->get()
                    ->map(function ($driver) use ($today) {
                        // Cek apakah driver sedang berjalan hari ini
                        $sedangBerjalan = DistribusiSekolah::where('pengirim', $driver->id)
                            ->whereDate('tanggal_harian', $today)
                            ->where('status', 'dikirim')
                            ->exists();

                        $driver->sedang_berjalan = $sedangBerjalan;
                        return $driver;
                    });

        $sekolahAktif = Sekolah::aktif()->orderBy('nama_sekolah')->get();

        $assignedSekolah = Pengiriman::whereIn('driver_id', $drivers->pluck('id'))
                                ->get()
                                ->groupBy('driver_id')
                                ->map(fn($items) => $items->pluck('sekolah_id')->toArray())
                                ->toArray();
        
        $allAssignedSekolah = Pengiriman::pluck('sekolah_id')->toArray();

        $this->sinkronisasiPengirimHariIni();

        return view('admin.monitoring.map', compact('drivers', 'sekolahAktif', 'assignedSekolah', 'allAssignedSekolah'));
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

    public function storePengiriman(Request $request)
    {
        $request->validate([
            'driver_id'     => 'required|exists:users,id',
            'sekolah_ids'   => 'required|array',
            'sekolah_ids.*' => 'exists:sekolah,id',
        ]);

        $driverId   = $request->driver_id;
        $sekolahIds = $request->sekolah_ids;

        // Cek konflik dengan driver lain
        $conflicts = Pengiriman::whereIn('sekolah_id', $sekolahIds)
                            ->where('driver_id', '!=', $driverId)
                            ->pluck('sekolah_id')
                            ->unique();

        if ($conflicts->isNotEmpty()) {
            $conflictNames = Sekolah::whereIn('id', $conflicts)->pluck('nama_sekolah')->implode(', ');
            return back()->withErrors(['sekolah_ids' => "Sekolah berikut sudah diassign ke driver lain: {$conflictNames}"]);
        }
        
        // Hapus assignment lama milik driver ini saja
        Pengiriman::where('driver_id', $driverId)->delete();

        foreach ($sekolahIds as $sekolahId) {
            Pengiriman::create([
                'driver_id'  => $driverId,
                'sekolah_id' => $sekolahId,
            ]);
        }

        // Update pengirim di SEMUA distribusi yang belum dikirim untuk sekolah ini
        // (bukan hanya hari ini, tapi seluruh distribusi yang masih draf)
        foreach ($sekolahIds as $sekolahId) {
            DistribusiSekolah::where('id_sekolah', $sekolahId)
                ->where('status', 'draf')
                ->update([
                    'pengirim' => $driverId,
                ]);
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