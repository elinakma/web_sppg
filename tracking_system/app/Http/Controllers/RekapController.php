<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Distribusi;
use App\Models\DistribusiSekolah;
use App\Models\MenuMakanan;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapController extends Controller
{
    /**
     * Tampilkan halaman rekap dengan filter periode
     */
    public function index(Request $request)
    {
        // Ambil semua periode distribusi untuk dropdown filter
        $periodeList = Distribusi::orderBy('tanggal_awal', 'desc')->get();

        // Jika ada id_distribusi di request, gunakan itu; default ke yang terbaru
        $selectedId = $request->get('id_distribusi', $periodeList->first()?->id);
        $distribusi = $selectedId ? Distribusi::find($selectedId) : null;

        $rekapData = null;

        if ($distribusi) {
            $rekapData = $this->buildRekapData($distribusi);
        }

        return view('rekap.index', compact('periodeList', 'distribusi', 'rekapData', 'selectedId'));
    }

    /**
     * Build semua data yang diperlukan untuk tampilan rekap
     */
    private function buildRekapData(Distribusi $distribusi): array
    {
        $tanggalAwal  = Carbon::parse($distribusi->tanggal_awal)->format('Y-m-d');
        $tanggalAkhir = Carbon::parse($distribusi->tanggal_akhir)->format('Y-m-d');

        // ── 1. Data Distribusi Harian ────────────────────────────────────────
        $hariDistribusi = DistribusiSekolah::where('id_distribusi', $distribusi->id)
            ->selectRaw('
                tanggal_harian,
                SUM(porsi_kecil_harian)  as total_porsi_kecil,
                SUM(porsi_besar_harian)  as total_porsi_besar,
                SUM(pagu_harian_sekolah) as total_pagu_harian,
                COUNT(DISTINCT id_sekolah) as jumlah_sekolah
            ')
            ->groupBy('tanggal_harian')
            ->orderBy('tanggal_harian', 'asc')
            ->get();

        // ── 2. Menu Makanan per tanggal ──────────────────────────────────────
        $menuPerTanggal = MenuMakanan::with('bahan')
            ->where('tanggal_menu', '>=', $tanggalAwal)
            ->where('tanggal_menu', '<=', $tanggalAkhir)
            ->get()
            ->groupBy(fn($m) => $m->tanggal_menu->format('Y-m-d'))
            ->map(fn($items) => $items->groupBy('jenis_porsi'));

        // ── 3. Hitung RAB per hari ───────────────────────────────────────────
        $rabHarian = [];
        $totalKebutuhan  = 0;
        $totalPaguHarian = 0;

        foreach ($hariDistribusi as $hari) {
            $tgl      = Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
            $namaHari = Carbon::parse($tgl)->locale('id')->isoFormat('dddd');
            $menuHari = $menuPerTanggal->get($tgl, collect());

            // Hitung total kebutuhan (harga bahan)
            $kebutuhan = 0;
            foreach (['kecil', 'besar'] as $jenis) {
                foreach ($menuHari->get($jenis, collect()) as $menu) {
                    foreach ($menu->bahan as $bahan) {
                        $kebutuhan += ($bahan->jumlah ?? 0) * ($bahan->harga_satuan ?? 0);
                    }
                }
            }

            $paguHarian = $hari->total_pagu_harian ?? 0;
            $selisih    = $paguHarian - $kebutuhan;

            $rabHarian[] = [
                'tanggal'     => $tgl,
                'nama_hari'   => strtoupper($namaHari),
                'kebutuhan'   => $kebutuhan,
                'pagu_harian' => $paguHarian,
                'selisih'     => $selisih,
            ];

            $totalKebutuhan  += $kebutuhan;
            $totalPaguHarian += $paguHarian;
        }

        $totalSelisih = $totalPaguHarian - $totalKebutuhan;

        // ── 4. Data Menu per Hari ────────────────────────────────────────────
        $menuHarian = [];
        foreach ($hariDistribusi as $hari) {
            $tgl      = Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
            $namaHari = strtoupper(Carbon::parse($tgl)->locale('id')->isoFormat('dddd'));
            $menuHari = $menuPerTanggal->get($tgl, collect());

            $menuKecil = $menuHari->get('kecil', collect());
            $menuBesar = $menuHari->get('besar', collect());

            // Kumpulkan nama-nama menu untuk tampilan ringkas (seperti di laporan)
            $namaMenuKecil = [];
            foreach ($menuKecil as $menu) {
                $namaMenuKecil[] = $menu->nama_menu;
                foreach ($menu->bahan as $bahan) {
                    $namaMenuKecil[] = '  · ' . $bahan->nama_bahan
                        . ($bahan->jumlah ? ' (' . $bahan->jumlah . ' ' . $bahan->satuan . ')' : '');
                }
            }

            $namaMenuBesar = [];
            foreach ($menuBesar as $menu) {
                $namaMenuBesar[] = $menu->nama_menu;
                foreach ($menu->bahan as $bahan) {
                    $namaMenuBesar[] = '  · ' . $bahan->nama_bahan
                        . ($bahan->jumlah ? ' (' . $bahan->jumlah . ' ' . $bahan->satuan . ')' : '');
                }
            }

            if ($menuKecil->isNotEmpty() || $menuBesar->isNotEmpty()) {
                $menuHarian[] = [
                    'tanggal'       => $tgl,
                    'nama_hari'     => $namaHari,
                    'menu_kecil'    => $namaMenuKecil,
                    'menu_besar'    => $namaMenuBesar,
                ];
            }
        }

        // ── 5. Ringkasan PM (Penerima Manfaat) ──────────────────────────────
        $totalPorsiKecil = $hariDistribusi->sum('total_porsi_kecil');
        $totalPorsiBesar = $hariDistribusi->sum('total_porsi_besar');
        $totalPenerima   = $totalPorsiKecil + $totalPorsiBesar;

        // Jumlah sekolah unik di seluruh periode ini
        $jumlahSekolah = DistribusiSekolah::where('id_distribusi', $distribusi->id)
            ->distinct('id_sekolah')
            ->count('id_sekolah');

        return [
            'distribusi'      => $distribusi,
            'rabHarian'       => $rabHarian,
            'totalKebutuhan'  => $totalKebutuhan,
            'totalPaguHarian' => $totalPaguHarian,
            'totalSelisih'    => $totalSelisih,
            'menuHarian'      => $menuHarian,
            'porsiKecil'      => $totalPorsiKecil,
            'porsiBesar'      => $totalPorsiBesar,
            'totalPenerima'   => $totalPenerima,
            'jumlahSekolah'   => $jumlahSekolah,
            'tglAwalFmt'      => Carbon::parse($distribusi->tanggal_awal)
                                    ->locale('id')->isoFormat('dddd, D MMMM Y'),
            'tglAkhirFmt'     => Carbon::parse($distribusi->tanggal_akhir)
                                    ->locale('id')->isoFormat('dddd, D MMMM Y'),
        ];
    }

    public function cetakRekap($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        // Build data yang sama seperti di index
        $rekapData = $this->buildRekapData($distribusi);

        try {
            ini_set('memory_limit', '2048M');

            $pdf = \PDF::loadView('rekap.rekap-pdf', compact('rekapData', 'distribusi'));

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 120,
            ]);

            $filename = "Rekap_Distribusi_" . 
                        Carbon::parse($distribusi->tanggal_awal)->format('d-m-Y') . 
                        "_sd_" . 
                        Carbon::parse($distribusi->tanggal_akhir)->format('d-m-Y') . 
                        ".pdf";

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('Error generate PDF Rekap', [
                'distribusi_id' => $id,
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal generate PDF. Silakan coba lagi.');
        }
    }
}