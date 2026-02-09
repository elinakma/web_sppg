<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distribusi;
use App\Models\Sekolah;
use App\Models\DistribusiSekolah;
use App\Models\Pagu;

class DistribusiController extends Controller
{
    public function create()
    {
        return view('admin.distribusi.tambah-distribusi');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_distribusi' => 'required|date'
        ]);

        Distribusi::create([
            'tanggal_distribusi' => $request->tanggal_distribusi,
            'status' => 'draft'
        ]);

        return redirect()->route('distribusi.index')
            ->with('success', 'Tanggal distribusi berhasil ditambahkan');
    }

    public function index()
    {
        $distribusi = Distribusi::orderBy('tanggal_distribusi', 'desc')->get();
        return view('admin.distribusi.kelola-distribusi', compact('distribusi'));
    }

    public function kelolaTotal($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        $sekolahAktif = Sekolah::aktif()->get();

        $pagu = Pagu::getPaguAktif();

        // Data real dari database (jika sudah disimpan)
        $dataDistribusiRaw = DistribusiSekolah::where('id_distribusi', $id)->get();

        $dataDistribusi = $dataDistribusiRaw->groupBy('tanggal_harian')
            ->map(fn($items) => $items->keyBy('id_sekolah'));

        $summaryHarian = $dataDistribusiRaw->groupBy('tanggal_harian')->map(function ($items) use ($pagu) {
            $totalKecil = $items->sum('porsi_kecil_harian');
            $totalBesar = $items->sum('porsi_besar_harian');
            $paguHarian = ($totalKecil * $pagu->pagu_porsi_kecil) + ($totalBesar * $pagu->pagu_porsi_besar);

            return [
                'total_porsi_kecil' => $totalKecil,
                'total_porsi_besar' => $totalBesar,
                'pagu_harian'       => $paguHarian,
            ];
        })->toArray();

        // Preview default (jika belum ada data real di hari itu)
        $previewHarian = [];
        $start = \Carbon\Carbon::parse($distribusi->tanggal_distribusi);
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        foreach ($hari as $index => $namaHari) {
            $tanggal = $start->clone()->addDays($index);
            $tanggalStr = $tanggal->format('Y-m-d');

            $totalKecil = $sekolahAktif->sum('porsi_kecil_default');
            $totalBesar = $sekolahAktif->sum('porsi_besar_default');
            $paguHarian = ($totalKecil * $pagu->pagu_porsi_kecil) + ($totalBesar * $pagu->pagu_porsi_besar);

            $previewHarian[$tanggalStr] = [
                'total_porsi_kecil' => $totalKecil,
                'total_porsi_besar' => $totalBesar,
                'pagu_harian'       => $paguHarian,
            ];
        }

        return view('admin.distribusi.tindak-lanjut', compact(
            'distribusi',
            'sekolahAktif',
            'dataDistribusi',
            'summaryHarian',
            'previewHarian',
            'pagu'
        ));
    }
    
    public function simpanTotal(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'id_distribusi' => 'required|exists:distribusi,id',
            'sekolah' => 'required|array',
            'sekolah.*.*.id_sekolah' => 'required|exists:sekolah,id',
            'sekolah.*.*.tanggal_harian' => 'required|date',
            'sekolah.*.*.porsi_kecil_harian' => 'required|integer|min:0',
            'sekolah.*.*.porsi_besar_harian' => 'required|integer|min:0',
            'sekolah.*.*.jenis_menu' => 'required|in:kering,basah',
            'sekolah.*.*.keterangan' => 'nullable|string|max:500',
        ]);

        $id_distribusi = $request->id_distribusi;
        $savedCount = 0;

        foreach ($request->sekolah as $sekolahData) {
            foreach ($sekolahData as $tanggalData) {
                $sekolah = Sekolah::findOrFail($tanggalData['id_sekolah']);
                $tanggalHarian = $tanggalData['tanggal_harian'];

                $porsiKecil = (int) ($tanggalData['porsi_kecil_harian'] ?? $sekolah->porsi_kecil_default);
                $porsiBesar = (int) ($tanggalData['porsi_besar_harian'] ?? $sekolah->porsi_besar_default);
                $totalPenerima = $porsiKecil + $porsiBesar;

                $record = DistribusiSekolah::updateOrCreate(
                    [
                        'id_distribusi' => $id_distribusi,
                        'id_sekolah' => $tanggalData['id_sekolah'],
                        'tanggal_harian' => $tanggalHarian,
                    ],
                    [
                        'porsi_kecil_harian' => $porsiKecil,
                        'porsi_besar_harian' => $porsiBesar,
                        'menu_kering' => $tanggalData['jenis_menu'] === 'kering' ? $totalPenerima : 0,
                        'menu_basah' => $tanggalData['jenis_menu'] === 'basah' ? $totalPenerima : 0,
                        'total_penerima' => $totalPenerima,
                        'keterangan' => $tanggalData['keterangan'] ?? null,
                    ]
                );

                $savedCount++;
            }
        }

        return redirect()->route('distribusi.index')
            ->with('success', "Berhasil simpan $savedCount record distribusi harian.");
    }

    public function cetakBeritaAcara($distribusiId)
    {
        $distribusi = Distribusi::findOrFail($distribusiId);

        // Ambil semua data distribusi sekolah untuk minggu ini, group by hari & sekolah
        $distribusiSekolah = DistribusiSekolah::with('sekolah')
            ->where('id_distribusi', $distribusiId)
            ->orderBy('tanggal_harian')
            ->get();

        if ($distribusiSekolah->isEmpty()) {
            return back()->with('error', 'Belum ada data sekolah untuk distribusi ini.');
        }

        $pdf = \PDF::loadView('admin.distribusi.berita-acara-pdf', compact(
            'distribusi',
            'distribusiSekolah'
        ));

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        $filename = "Berita_Acara_Distribusi_" . $distribusi->tanggal_distribusi->format('d-m-Y') . ".pdf";

        return $pdf->stream($filename);
    }
}


