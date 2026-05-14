<?php

namespace App\Http\Controllers;

use App\Models\MenuMakanan;
use App\Models\BahanMakanan;
use App\Models\DistribusiSekolah;
use App\Models\AkgHarian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MenuMakananController extends Controller
{
    // ── Halaman Kelola Menu (per hari distribusi) ────────────────────────
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

        $menuPerTanggal = MenuMakanan::with('bahan')
            ->whereNotNull('tanggal_menu')
            ->whereIn('tanggal_menu', $hariDistribusi->pluck('tanggal_harian'))
            ->get()
            ->groupBy(fn($m) => $m->tanggal_menu->format('Y-m-d'))
            ->map(fn($items) => $items->groupBy('jenis_porsi'));

        // === DATA UNTUK TEMPLATE SEARCH ===
        $allTemplates = MenuMakanan::with('bahan')
            ->whereNull('tanggal_menu')
            ->orderBy('jenis_porsi')
            ->orderBy('nama_menu')
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'nama_menu'   => $t->nama_menu,
                'jenis_porsi' => $t->jenis_porsi,
                'bahan'       => $t->bahan->map(fn($b) => [
                    'nama_bahan' => $b->nama_bahan,
                    'jumlah'     => $b->jumlah,
                    'satuan'     => $b->satuan,
                ])->toArray(),
            ])
            ->toArray();

        // AKG
        $akgHarian = AkgHarian::whereIn('tanggal_harian', $hariDistribusi->pluck('tanggal_harian'))
            ->get()
            ->keyBy(fn($item) => $item->tanggal_harian->format('Y-m-d'));

        return view('gizi.menu.kelola-menu', compact('hariDistribusi', 'menuPerTanggal', 'allTemplates', 'akgHarian'));
    }

    // ── Halaman Kelola Template (menu dengan tanggal_menu = null) ────────
    public function template()
    {
        $templates = MenuMakanan::with('bahan')
            ->whereNull('tanggal_menu')
            ->orderBy('jenis_porsi')
            ->orderBy('nama_menu')
            ->get()
            ->groupBy('jenis_porsi');

        return view('gizi.menu.kelola-template', compact('templates'));
    }

    // ── API: ambil bahan dari satu template (untuk auto-fill JS) ─────────
    public function templateDetail(MenuMakanan $menu)
    {
        if ($menu->tanggal_menu !== null) {
            return response()->json(['error' => 'Bukan template'], 400);
        }

        return response()->json([
            'id'         => $menu->id,
            'nama_menu'  => $menu->nama_menu,
            'jenis_porsi'=> $menu->jenis_porsi,
            'bahan'      => $menu->bahan->map(fn($b) => [
                'nama_bahan' => $b->nama_bahan,
                'jumlah'     => $b->jumlah,
                'satuan'     => $b->satuan,
            ]),
        ]);
    }

    // ── Simpan Menu Harian (+ opsional simpan sebagai template) ──────────
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_menu'       => 'required|date',
            'jenis_porsi'        => 'required|in:kecil,besar',
            'nama_menu'          => 'required|string|max:255',
            'bahan'              => 'required|array|min:1',
            'bahan.*.nama_bahan' => 'required|string|max:255',
            'bahan.*.jumlah'     => 'required|numeric|min:0',
            'bahan.*.satuan'     => 'required|string|max:50',
        ]);

        // Simpan menu harian
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

        // Simpan sebagai template jika dicentang dan belum ada template sama
        if ($request->boolean('simpan_sebagai_template')) {
            $sudahAda = MenuMakanan::whereNull('tanggal_menu')
                ->where('nama_menu', $request->nama_menu)
                ->where('jenis_porsi', $request->jenis_porsi)
                ->exists();

            if (!$sudahAda) {
                $template = MenuMakanan::create([
                    'tanggal_menu' => null,
                    'jenis_porsi'  => $request->jenis_porsi,
                    'nama_menu'    => $request->nama_menu,
                ]);

                foreach ($request->bahan as $bahan) {
                    BahanMakanan::create([
                        'menu_makanan_id' => $template->id,
                        'nama_bahan'      => $bahan['nama_bahan'],
                        'jumlah'          => $bahan['jumlah'],
                        'satuan'          => $bahan['satuan'],
                    ]);
                }
            }
        }

        return redirect()->route('gizi.menu.index')
            ->with('success', 'Menu makanan berhasil ditambahkan.');
    }

    // ── Simpan Template Baru ─────────────────────────────────────────────
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'jenis_porsi'        => 'required|in:kecil,besar',
            'nama_menu'          => 'required|string|max:255',
            'bahan'              => 'required|array|min:1',
            'bahan.*.nama_bahan' => 'required|string|max:255',
            'bahan.*.jumlah'     => 'required|numeric|min:0',
            'bahan.*.satuan'     => 'required|string|max:50',
        ]);

        $template = MenuMakanan::create([
            'tanggal_menu' => null,
            'jenis_porsi'  => $request->jenis_porsi,
            'nama_menu'    => $request->nama_menu,
        ]);

        foreach ($request->bahan as $bahan) {
            BahanMakanan::create([
                'menu_makanan_id' => $template->id,
                'nama_bahan'      => $bahan['nama_bahan'],
                'jumlah'          => $bahan['jumlah'],
                'satuan'          => $bahan['satuan'],
            ]);
        }

        return redirect()->route('gizi.template.index')
            ->with('success', 'Template menu berhasil ditambahkan.');
    }

    // ── Update Menu Harian atau Template ─────────────────────────────────
    public function update(Request $request, MenuMakanan $menu)
    {
        $request->validate([
            'nama_menu'          => 'required|string|max:255',
            'jenis_porsi'        => 'required|in:kecil,besar',
            'bahan'              => 'required|array|min:1',
            'bahan.*.nama_bahan' => 'required|string|max:255',
            'bahan.*.jumlah'     => 'required|numeric|min:0',
            'bahan.*.satuan'     => 'required|string|max:50',
        ]);

        $menu->update([
            'nama_menu'   => $request->nama_menu,
            'jenis_porsi' => $request->jenis_porsi,
        ]);

        $menu->bahan()->delete();

        foreach ($request->bahan as $bahan) {
            BahanMakanan::create([
                'menu_makanan_id' => $menu->id,
                'nama_bahan'      => $bahan['nama_bahan'],
                'jumlah'          => $bahan['jumlah'],
                'satuan'          => $bahan['satuan'],
            ]);
        }

        // Redirect kembali ke halaman yang sesuai
        $route = $menu->tanggal_menu ? 'gizi.menu.index' : 'gizi.template.index';

        return redirect()->route($route)
            ->with('success', 'Menu berhasil diperbarui.');
    }

    // ── Hapus Menu Harian atau Template (beserta bahan) ──────────────────
    public function destroy(MenuMakanan $menu)
    {
        $isTemplate = $menu->tanggal_menu === null;

        $menu->bahan()->delete();
        $menu->delete();

        $route = $isTemplate ? 'gizi.template.index' : 'gizi.menu.index';

        return redirect()->route($route)
            ->with('success', 'Menu berhasil dihapus.');
    }

    // AKG Harian
    public function storeAkgHarian(Request $request)
    {
        $request->validate([
            'tanggal_harian'     => 'required|date',
            'energi_kecil'       => 'nullable|integer|min:0',
            'protein_kecil'      => 'nullable|numeric|min:0',
            'lemak_kecil'        => 'nullable|numeric|min:0',
            'karbo_kecil'        => 'nullable|numeric|min:0',
            'serat_kecil'        => 'nullable|numeric|min:0',
            'energi_besar'       => 'nullable|numeric|min:0',
            'protein_besar'      => 'nullable|numeric|min:0',
            'lemak_besar'        => 'nullable|numeric|min:0',
            'karbo_besar'        => 'nullable|numeric|min:0',
            'serat_besar'        => 'nullable|numeric|min:0',
        ]);

        AkgHarian::updateOrCreate(
            ['tanggal_harian' => $request->tanggal_harian],
            $request->only([
                'energi_kecil','karbo_kecil','lemak_kecil','protein_kecil','serat_kecil',
                'energi_besar','karbo_besar','lemak_besar','protein_besar','serat_besar',
            ])
        );

        return redirect()->back()
            ->with('success', 'AKG harian berhasil disimpan untuk tanggal tersebut.');
    }
}