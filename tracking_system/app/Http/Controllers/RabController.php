<?php

namespace App\Http\Controllers;

use App\Models\MenuMakanan;
use App\Models\BahanMakanan;
use App\Models\Distribusi;
use App\Models\DistribusiSekolah;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RabController extends Controller
{
    public function index()
    {
        // Ambil semua periode distribusi yang sudah ada
        $periodeDistribusi = Distribusi::orderBy('tanggal_awal', 'desc')->get();

        // Untuk setiap periode, ambil data hari yang sudah memiliki menu & bahan
        $dataPerPeriode = [];

        foreach ($periodeDistribusi as $distribusi) {
            $tanggalAwal  = Carbon::parse($distribusi->tanggal_awal)->format('Y-m-d');
            $tanggalAkhir = Carbon::parse($distribusi->tanggal_akhir)->format('Y-m-d');

            // Ambil hari distribusi (sudah diinput admin) dalam rentang periode ini
            $hariDistribusi = DistribusiSekolah::where('id_distribusi', $distribusi->id)
                ->selectRaw('
                    tanggal_harian,
                    SUM(porsi_kecil_harian)    as total_porsi_kecil,
                    SUM(porsi_besar_harian)    as total_porsi_besar,
                    SUM(pagu_harian_sekolah)   as total_pagu_harian
                ')
                ->groupBy('tanggal_harian')
                ->orderBy('tanggal_harian', 'asc')
                ->get();

            // Ambil tanggal yang sudah ada menu & bahan
            $tanggalDenganMenu = MenuMakanan::with('bahan')
                ->where('tanggal_menu', '>=', $tanggalAwal)
                ->where('tanggal_menu', '<=', $tanggalAkhir)
                ->get()
                ->groupBy(fn($m) => $m->tanggal_menu->format('Y-m-d'))
                ->map(fn($items) => $items->groupBy('jenis_porsi'));

            // Hitung ringkasan periode
            $totalPaguPeriode  = $hariDistribusi->sum('total_pagu_harian');
            $totalHargaPeriode = 0;

            foreach ($hariDistribusi as $hari) {
                $tgl      = Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
                $menuHari = $tanggalDenganMenu->get($tgl, collect());

                $totalHariIni = 0;
                foreach (['kecil', 'besar'] as $jenis) {
                    foreach ($menuHari->get($jenis, collect()) as $menu) {
                        foreach ($menu->bahan as $bahan) {
                            $totalHariIni += ($bahan->jumlah ?? 0) * ($bahan->harga_satuan ?? 0);
                        }
                    }
                }
                $totalHargaPeriode += $totalHariIni;
            }

            // Hanya masukkan periode yang minimal 1 hari punya menu
            $adaMenu = $hariDistribusi->filter(function ($hari) use ($tanggalDenganMenu) {
                $tgl = Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
                return $tanggalDenganMenu->has($tgl);
            })->count();

            if ($adaMenu > 0) {
                $dataPerPeriode[] = [
                    'distribusi'        => $distribusi,
                    'hariDistribusi'    => $hariDistribusi,
                    'menuPerTanggal'    => $tanggalDenganMenu,
                    'totalPaguPeriode'  => $totalPaguPeriode,
                    'totalHargaPeriode' => $totalHargaPeriode,
                    'selisihPeriode'    => $totalPaguPeriode - $totalHargaPeriode,
                ];
            }
        }

        return view('akuntan.rab.kelola-rab', compact('dataPerPeriode'));
    }

    public function updateHarga(Request $request, BahanMakanan $bahan)
    {
        $request->validate([
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $bahan->update(['harga_satuan' => $request->harga_satuan]);

        return redirect()->route('akuntan.rab.index')
            ->with('success', 'Harga bahan berhasil diperbarui.');
    }

    public function updateHargaBulk(Request $request)
    {
        $request->validate([
            'bahan'                => 'required|array',
            'bahan.*.id'           => 'required|exists:bahan_makanan,id',
            'bahan.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        foreach ($request->bahan as $item) {
            BahanMakanan::where('id', $item['id'])
                        ->update(['harga_satuan' => $item['harga_satuan']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Harga bahan berhasil diperbarui.'
        ]);
    }

    public function preOrder()
    {
        return view('akuntan.rab.pre-order');
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);

        $tanggalAwal  = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;

        $hariDistribusi = DistribusiSekolah::where('tanggal_harian', '>=', $tanggalAwal)
            ->where('tanggal_harian', '<=', $tanggalAkhir)
            ->selectRaw('
                tanggal_harian,
                SUM(porsi_kecil_harian)  as total_porsi_kecil,
                SUM(porsi_besar_harian)  as total_porsi_besar,
                SUM(pagu_harian_sekolah) as total_pagu_harian
            ')
            ->groupBy('tanggal_harian')
            ->orderBy('tanggal_harian')
            ->get();

        $menuPerTanggal = MenuMakanan::with('bahan')
            ->where('tanggal_menu', '>=', $tanggalAwal)
            ->where('tanggal_menu', '<=', $tanggalAkhir)
            ->get()
            ->groupBy(fn($m) => $m->tanggal_menu->format('Y-m-d'))
            ->map(fn($items) => $items->groupBy('jenis_porsi'));

        $dataPdf = [];

        foreach ($hariDistribusi as $hari) {
            $tgl      = Carbon::parse($hari->tanggal_harian)->format('Y-m-d');
            $menuHari = $menuPerTanggal->get($tgl, collect());
            $items    = [];

            foreach (['kecil', 'besar'] as $jenis) {
                foreach ($menuHari->get($jenis, collect()) as $menu) {
                    foreach ($menu->bahan as $bahan) {
                        $items[] = [
                            'tanggal'    => $tgl,
                            'tgl_fmt'    => Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM Y'),
                            'nama_bahan' => $bahan->nama_bahan,
                            'jumlah'     => $bahan->jumlah ?? 0,
                            'satuan'     => $bahan->satuan ?? '-',
                        ];
                    }
                }
            }

            if (count($items) > 0) {
                $dataPdf[] = [
                    'tgl_fmt' => Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM Y'),
                    'items'   => $items,
                ];
            }
        }

        if (empty($dataPdf)) {
            return redirect()->route('akuntan.rab.pre-order')
                ->with('error', 'Tidak ada data RAB pada periode tersebut.');
        }

        $pdf = Pdf::loadView('akuntan.rab.pdf-rab', [
            'dataPdf'      => $dataPdf,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("RAB_{$tanggalAwal}_sampai_{$tanggalAkhir}.pdf");
    }
}