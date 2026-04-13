<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distribusi;
use App\Models\Sekolah;
use App\Models\DistribusiSekolah;
use App\Models\Pagu;
use Illuminate\Support\Facades\Log;

class DistribusiController extends Controller
{
    public function index()
    {
        $distribusi = Distribusi::orderBy('tanggal_awal', 'desc')
            ->paginate(8);

        // status display untuk setiap distribusi
        foreach ($distribusi as $item) {
            $totalRecords = DistribusiSekolah::where('id_distribusi', $item->id)->count();
            $selesai = DistribusiSekolah::where('id_distribusi', $item->id)
                        ->where('status', 'selesai')->count();
            $dikirim = DistribusiSekolah::where('id_distribusi', $item->id)
                        ->where('status', 'dikirim')->count();

            if ($totalRecords == 0) {
                $item->status_display = 'Draf';
                $item->status_color = 'bg-primary';
            } elseif ($selesai == $totalRecords) {
                $item->status_display = 'Selesai';
                $item->status_color = 'bg-success';
            } elseif ($dikirim > 0 || $selesai > 0) {
                $item->status_display = 'Diproses';
                $item->status_color = 'bg-warning text-dark';
            } else {
                $item->status_display = 'Draf';
                $item->status_color = 'bg-primary';
            }
        }
            
        return view('admin.distribusi.kelola-distribusi', compact('distribusi'));
    }

    public function create()
    {
        return view('admin.distribusi.tambah-distribusi');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);

        Distribusi::create([
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'status' => 'draf'
        ]);

        return redirect()->route('admin.distribusi.index')
            ->with('success', 'Tanggal distribusi berhasil ditambahkan');
    }

    public function kelolaTotal($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        $sekolahAktif = Sekolah::aktif()->get();

        // Ambil ID sekolah yang sudah tersimpan di distribusi ini
        $idSekolahTersimpan = DistribusiSekolah::where('id_distribusi', $id)
            ->distinct()
            ->pluck('id_sekolah');

        // Sekolah yang sudah tersimpan tapi sekarang nonaktif
        $sekolahNonaktifTersimpan = Sekolah::whereIn('id', $idSekolahTersimpan)
            ->where('status', '!=', 'Aktif')
            ->get();

        // Gabungkan: sekolah aktif + sekolah nonaktif yang sudah tersimpan di distribusi ini
        $sekolahAktif = $sekolahAktif->merge($sekolahNonaktifTersimpan)->unique('id');

        $pagu = Pagu::getPaguAktif();

        // Mengambil semua hari dari tanggal awal - akhir
        $start = \Carbon\Carbon::parse($distribusi->tanggal_awal);
        $end = \Carbon\Carbon::parse($distribusi->tanggal_akhir);
        $hariList = [];
        while ($start->lte($end)) {
            $hariList[] = $start->format('Y-m-d');
            $start->addDay();
        }

        // Data Real Distribusi Sekolah
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
        $start = \Carbon\Carbon::parse($distribusi->tanggal_awal);
        foreach ($hariList as $tanggalStr) {
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
            'hariList',
            'summaryHarian',
            'previewHarian',
            'pagu'
        ));
    }
    
    public function simpanTotal(Request $request)
    {
        Log::debug('simpanTotal called', ['request' => $request->all()]); /// Cek data request masuk

        try {
            $validated = $request->validate([
                'id_distribusi' => 'required|exists:distribusi,id',
                'sekolah' => 'required|array',
                'sekolah.*' => 'array',
                'sekolah.*.*' => 'array',
                'sekolah.*.*.id_sekolah' => 'required|exists:sekolah,id',
                'sekolah.*.*.tanggal_harian' => 'required|date',
                'sekolah.*.*.porsi_kecil_harian' => 'nullable|integer|min:0',
                'sekolah.*.*.porsi_besar_harian' => 'nullable|integer|min:0',
                'sekolah.*.*.jenis_menu' => 'nullable|in:kering,basah',
                'sekolah.*.*.keterangan' => 'nullable|string|max:500',
            ]);

            Log::debug('Validasi sukses'); // Validasi

            $id_distribusi = $request->id_distribusi;
            $pagu = Pagu::getPaguAktif();

            $saved = 0;

            foreach ($request->sekolah as $id_sekolah => $dataTanggal) {
                Log::debug('Mulai loop sekolah', ['id_sekolah' => $id_sekolah, 'dataTanggal' => $dataTanggal]); // Cek loop sekolah

                foreach ($dataTanggal as $tanggal_harian => $data) {
                    Log::debug('Mulai sub-loop tanggal', ['tanggal_harian' => $tanggal_harian, 'data' => $data]); // Cek data per tanggal

                    $existing = DistribusiSekolah::where([
                        'id_distribusi' => $id_distribusi,
                        'id_sekolah' => $id_sekolah,
                        'tanggal_harian' => $tanggal_harian,
                    ])->first();

                    $porsiKecil = (int) ($data['porsi_kecil_harian'] ?? ($existing?->porsi_kecil_harian ?? 0));
                    $porsiBesar = (int) ($data['porsi_besar_harian'] ?? ($existing?->porsi_besar_harian ?? 0));
                    $totalPenerima = $porsiKecil + $porsiBesar;

                    $jenisMenu = $data['jenis_menu'] ?? ($existing?->menu_kering > 0 ? 'kering' : ($existing?->menu_basah > 0 ? 'basah' : 'kering'));

                    $paguHarianSekolah = ($porsiKecil * $pagu->pagu_porsi_kecil) + ($porsiBesar * $pagu->pagu_porsi_besar);

                    $record = DistribusiSekolah::updateOrCreate(
                        [
                            'id_distribusi' => $id_distribusi,
                            'id_sekolah' => $id_sekolah,
                            'tanggal_harian' => $tanggal_harian,
                        ],
                        [
                            'porsi_kecil_harian' => $porsiKecil,
                            'porsi_besar_harian' => $porsiBesar,
                            'menu_kering' => $jenisMenu === 'kering' ? $totalPenerima : 0,
                            'menu_basah' => $jenisMenu === 'basah' ? $totalPenerima : 0,
                            'total_penerima' => $totalPenerima,
                            'pagu_harian_sekolah' => $paguHarianSekolah,
                            'keterangan' => $data['keterangan'] ?? $existing?->keterangan,
                        ]
                    );

                    $saved++;

                    Log::debug('Record saved', ['record_id' => $record->id, 'was_new' => $record->wasRecentlyCreated]); // Cek simpan sukses
                }
            }

            Log::debug('Loop selesai, total saved', ['saved' => $saved]); // Total simpan

            return redirect()->route('admin.distribusi.index')
                ->with('success', "Berhasil menyimpan $saved record distribusi sekolah.");
        } catch (\Exception $e) {
            Log::error('Error di simpanTotal', ['error' => $e->getMessage()]); // Catch error
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }

    // Detail
    public function detail($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        $sekolahAktif = Sekolah::aktif()->get();

        $pagu = Pagu::getPaguAktif();

        // Generate list hari dari tanggal_awal sampai tanggal_akhir
        $start = \Carbon\Carbon::parse($distribusi->tanggal_awal);
        $end = \Carbon\Carbon::parse($distribusi->tanggal_akhir);
        $hariList = [];
        $current = $start->clone();
        while ($current->lte($end)) {
            $hariList[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Data real distribusi sekolah
        $dataDistribusiRaw = DistribusiSekolah::where('id_distribusi', $id)
            ->with('sekolah')
            ->get();

        $dataDistribusi = $dataDistribusiRaw->groupBy('tanggal_harian')
            ->map(fn($items) => $items->keyBy('id_sekolah'));

        // Hitung summary per hari (real data)
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

        // Hitung grand total mingguan
        $grandTotalPagu = collect($summaryHarian)->sum('pagu_harian');

        return view('admin.distribusi.detail-distribusi', compact(
            'distribusi',
            'sekolahAktif',
            'dataDistribusi',
            'hariList',
            'summaryHarian',
            'grandTotalPagu',
            'pagu'
        ));
    }

    public function cetakBeritaAcara($distribusiId)
    {
        $distribusi = Distribusi::findOrFail($distribusiId);

        $distribusiSekolah = DistribusiSekolah::with('sekolah')
            ->where('id_distribusi', $distribusiId)
            ->orderBy('tanggal_harian')
            ->orderBy('id_sekolah')
            ->get();

        if ($distribusiSekolah->isEmpty()) {
            return back()->with('error', 'Belum ada data sekolah untuk distribusi ini.');
        }

        try {
            // Optimasi memory
            ini_set('memory_limit', '2048M');

            $pdf = \PDF::loadView('admin.distribusi.berita-acara-pdf', compact(
                'distribusi',
                'distribusiSekolah'
            ));

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 100,
                'isPhpEnabled' => false,
            ]);

            $filename = "Berita_Acara_Distribusi_" . 
                        $distribusi->tanggal_awal->format('d-m-Y') . ".pdf";

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('Error generate PDF Berita Acara', [
                'distribusi_id' => $distribusiId,
                'sekolah_count' => $distribusiSekolah->count(),
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal generate PDF. Data terlalu besar.');
        }
    }

    public function destroy($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        // Hapus semua data anak di distribusi_sekolah
        DistribusiSekolah::where('id_distribusi', $id)->delete();

        // Hapus data distribusi utama
        $distribusi->delete();

        return redirect()->route('admin.distribusi.index')
            ->with('success', 'Data distribusi berhasil dihapus.');
    }
}


