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
                    ->where('status', 'Aktif')
                    ->with(['location' => fn($q) => $q->latest()->limit(1)])
                    ->with('assignedSekolah')
                    ->get()
                    ->map(function ($driver) use ($today) {
                        $sedangBerjalan = DistribusiSekolah::where('pengirim', $driver->id)
                            ->whereDate('tanggal_harian', $today)
                            ->where('status', 'dikirim')
                            ->exists();
                        $driver->sedang_berjalan = $sedangBerjalan;
                        return $driver;
                    });
                    
        $sekolahSemua = Sekolah::orderByRaw("FIELD(status, 'Aktif', 'Nonaktif')")
                        ->orderBy('nama_sekolah')
                        ->get();
    
        // Gunakan withTrashed pada relasi agar sekolah nonaktif ikut terhitung
        $driverIds = $drivers->pluck('id');
    
        $assignedSekolah = Pengiriman::whereIn('driver_id', $driverIds)
                            ->with(['sekolah' => fn($q) => $q->withTrashed()])
                            ->get()
                            ->groupBy('driver_id')
                            ->map(fn($items) => $items->pluck('sekolah_id')->toArray())
                            ->toArray();
    
        $allAssignedSekolah = Pengiriman::whereIn('driver_id', $driverIds)
                            ->pluck('sekolah_id')
                            ->toArray();
    
        $this->sinkronisasiPengirimHariIni();
    
        return view('admin.monitoring.map', compact(
            'drivers', 'sekolahSemua'
        ));
    }

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
            'driver_id' => 'required|exists:users,id',
            'sekolah_ids' => 'nullable|array',
            'sekolah_ids.*' => 'integer|exists:sekolah,id',
        ]);
    
        $driverId = (int) $request->driver_id;
        $sekolahIds = array_map('intval', $request->sekolah_ids ?? []);
    
        $today = now()->toDateString();
    
        // Ambil assignment lama sebeum dihapus
        $oldSekolahIds = Pengiriman::where('driver_id', $driverId)
                            ->pluck('sekolah_id')
                            ->toArray();
    
        Pengiriman::where('driver_id', $driverId)->delete();
    
        DistribusiSekolah::where('pengirim', $driverId)
            ->whereDate('tanggal_harian', $today)
            ->where('status', 'draf')
            ->update(['pengirim' => null, 'status' => 'draf']);
    
        $conflicts = \DB::table('pengiriman')
                        ->whereIn('sekolah_id', $sekolahIds)
                        ->where('driver_id', '!=', $driverId)
                        ->exists();
    
        if ($conflicts) {
            // Rollback
            $rollbackData = [];
            foreach ($oldSekolahIds as $oldId) {
                $rollbackData[] = [
                    'driver_id' => $driverId,
                    'sekolah_id' => $oldId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($rollbackData)) {
                Pengiriman::insert($rollbackData);
            }
    
            return response()->json([
                'message' => 'Beberapa sekolah sudah diassign ke driver aktif lain.'
            ], 422);
        }
    
        $newAssignments = [];
        foreach ($sekolahIds as $sekolahId) {
            $newAssignments[] = [
                'driver_id' => $driverId,
                'sekolah_id' => $sekolahId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    
        if (!empty($newAssignments)) {
            Pengiriman::insert($newAssignments);
        }
    
        // Update distribusi
        if (!empty($sekolahIds)) {
            DistribusiSekolah::whereIn('id_sekolah', $sekolahIds)
                ->whereDate('tanggal_harian', $today)
                ->where('status', 'draf')
                ->update(['pengirim' => $driverId]);
        }
    
        return response()->json([
            'message' => 'Sekolah berhasil dibagikan ke driver.',
            'driver_id' => $driverId
        ]);
    }

    public function getAssignedSekolah()
    {
        $driverIds = User::where('role', 'Driver')->where('status', 'Aktif')->pluck('id');
    
        $assignedRaw = Pengiriman::whereIn('driver_id', $driverIds)
                        ->get()
                        ->groupBy('driver_id');
    
        // Cast key ke integer eksplisit
        $assignedSekolah = [];
        foreach ($assignedRaw as $driverId => $items) {
            $assignedSekolah[(int) $driverId] = $items->pluck('sekolah_id')->map(fn($id) => (int) $id)->toArray();
        }
    
        $allAssignedSekolah = Pengiriman::whereIn('driver_id', $driverIds)
                            ->pluck('sekolah_id')
                            ->map(fn($id) => (int) $id)
                            ->toArray();
    
        return response()->json([
            'assignedSekolah'    => $assignedSekolah,
            'allAssignedSekolah' => $allAssignedSekolah,
        ]);
    }

    public function destroyPengiriman(Pengiriman $pengiriman)
    {
        $pengiriman->delete();

        return redirect()->route('admin.monitoring.map')
                         ->with('success', 'Sekolah berhasil dihapus dari driver.');
    }
}