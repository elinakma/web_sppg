<?php

namespace App\Http\Controllers;

use App\Models\MenuMakanan;
use App\Models\BahanMakanan;
use App\Models\DistribusiSekolah;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MenuMakananController extends Controller
{
    public function index()
    {
        $hariDistribusi = DistribusiSekolah::selectRaw('
                tanggal_harian,
                SUM(porsi_kecil_harian) as total_porsi_kecil,
                SUM(porsi_besar_harian) as total_porsi_besar
            ')
            ->groupBy('tanggal_harian')
            ->orderBy('tanggal_harian', 'desc')
            ->get();

        // Ambil menu yang sudah ada per tanggal
        $menuPerTanggal = MenuMakanan::with('bahan')
            ->whereIn('tanggal_menu', $hariDistribusi->pluck('tanggal_harian'))
            ->get()
            ->groupBy(fn($m) => $m->tanggal_menu->format('Y-m-d'))
            ->map(fn($items) => $items->groupBy('jenis_porsi'));

        return view('gizi.menu.kelola-menu', compact('hariDistribusi', 'menuPerTanggal'));
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_menu'          => 'required|date',
            'jenis_porsi'           => 'required|in:kecil,besar',
            'nama_menu'             => 'required|string|max:255',
            'bahan'                 => 'required|array|min:1',
            'bahan.*.nama_bahan'    => 'required|string|max:255',
            'bahan.*.jumlah'        => 'required|numeric|min:0',
            'bahan.*.satuan'        => 'required|string|max:50',
        ]);

        $menu = MenuMakanan::create([
            'tanggal_menu' => $request->tanggal_menu,
            'jenis_porsi'  => $request->jenis_porsi,
            'nama_menu'    => $request->nama_menu,
        ]);

        foreach ($request->bahan as $bahan) {
            BahanMakanan::create([
                'menu_makanan_id' => $menu->id,
                'nama_bahan'      => $bahan['nama_bahan'],
                'jumlah'          => $bahan['jumlah'],
                'satuan'          => $bahan['satuan'],
            ]);
        }

        return redirect()->route('gizi.menu.index')
            ->with('success', 'Menu makanan berhasil ditambahkan.');
    }

    public function update(Request $request, MenuMakanan $menu)
    {
        $request->validate([
            'nama_menu'             => 'required|string|max:255',
            'bahan'                 => 'required|array|min:1',
            'bahan.*.nama_bahan'    => 'required|string|max:255',
            'bahan.*.jumlah'        => 'required|numeric|min:0',
            'bahan.*.satuan'        => 'required|string|max:50',
        ]);

        $menu->update(['nama_menu' => $request->nama_menu]);

        // Hapus bahan lama, ganti dengan yang baru
        $menu->bahan()->delete();

        foreach ($request->bahan as $bahan) {
            BahanMakanan::create([
                'menu_makanan_id' => $menu->id,
                'nama_bahan'      => $bahan['nama_bahan'],
                'jumlah'          => $bahan['jumlah'],
                'satuan'          => $bahan['satuan'],
            ]);
        }

        return redirect()->route('gizi.menu.index')
            ->with('success', 'Menu makanan berhasil diperbarui.');
    }

    public function destroy(MenuMakanan $menu)
    {
        $menu->bahan()->delete();
        $menu->delete();

        return redirect()->route('gizi.menu.index')
            ->with('success', 'Menu makanan berhasil dihapus.');
    }
}