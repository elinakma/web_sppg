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
        $drivers = User::where('role', 'Driver')
                       ->with(['locations' => function ($query) {
                           $query->latest()->limit(1);
                       }])
                       ->get();

        $sekolahAktif = Sekolah::aktif()->orderBy('nama_sekolah')->get();

        $assignedSekolah = Pengiriman::whereIn('driver_id', $drivers->pluck('id'))
                                 ->get()
                                 ->groupBy('driver_id')
                                 ->map(fn($items) => $items->pluck('sekolah_id')->toArray())
                                 ->toArray();
        
        $allAssignedSekolah = Pengiriman::pluck('sekolah_id')->toArray();

        return view('admin.monitoring.map', compact('drivers', 'sekolahAktif', 'assignedSekolah', 'allAssignedSekolah'));
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

        // Cek apakah ada sekolah yang sudah diassign ke driver lain
        $conflicts = Pengiriman::whereIn('sekolah_id', $sekolahIds)
                            ->where('driver_id', '!=', $driverId)
                            ->pluck('sekolah_id')
                            ->unique();

        if ($conflicts->isNotEmpty()) {
            $conflictNames = Sekolah::whereIn('id', $conflicts)->pluck('nama_sekolah')->implode(', ');
            return back()->withErrors(['sekolah_ids' => "Sekolah berikut sudah diassign ke driver lain: {$conflictNames}"]);
        }
        
        // Hapus assignment lama
        Pengiriman::where('driver_id', $driverId)->delete();

        // Simpan pembaruan assignment
        foreach ($sekolahIds as $sekolahId) {
            Pengiriman::create([
                'driver_id'   => $driverId,
                'sekolah_id'  => $sekolahId,
            ]);
            
            // Otomatis isi kolom pengirim di distribusi_sekolah untuk sekolah ini
            DistribusiSekolah::where('id_sekolah', $sekolahId)
                            ->update([
                                'pengirim' => $driverId,
                                'status'   => 'draf'
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