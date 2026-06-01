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
    public function index(Request $request)
    {
        $query = Distribusi::query();

        // Filter tanggal awal
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_awal', '>=', $request->tanggal_awal);
        }

        // Filter tanggal akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_akhir', '<=', $request->tanggal_akhir);
        }
        
        $distribusi = $query->orderBy('tanggal_awal', 'desc')
            ->paginate(8)
            ->withQueryString();

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
        ]);

        return redirect()->route('admin.distribusi.index')
            ->with('success', 'Tanggal distribusi berhasil ditambahkan');
    }

    public function kelolaTotal($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        // Ambil ID sekolah yang sudah tersimpan di distribusi ini
        $idSekolahTersimpan = DistribusiSekolah::where('id_distribusi', $id)
            ->distinct()
            ->pluck('id_sekolah');
        
        // Sekolah aktif saat ini
        $sekolahAktifSekarang = Sekolah::aktif()->get();

        // Sekolah yang sudah tersimpan tapi sekarang nonaktif atau sudah dihapus
        $sekolahNonaktifTersimpan = Sekolah::whereIn('id', $idSekolahTersimpan)
            ->withTrashed()
            ->get();

        // Gabungan akhir : historis + aktif sa at ini
        $sekolahAktif = $sekolahAktifSekarang
            ->merge($sekolahNonaktifTersimpan)
            ->unique('id')
            ->sortBy('nama_sekolah');

        $paguSekarang = Pagu::getPaguAktif();

        $dataDistribusiRaw = DistribusiSekolah::where('id_distribusi', $id)
            ->with('sekolah')
            ->get();
        
        $dataDistribusi = $dataDistribusiRaw->groupBy('tanggal_harian')
            ->map(fn($items) => $items->keyBy('id_sekolah'));

        // Snapshot pagu
        $firstRecord = $dataDistribusiRaw->first();
        $paguKecilEfektif = $firstRecord?->pagu_porsi_kecil ?? $paguSekarang->pagu_porsi_kecil;
        $paguBesarEfektif = $firstRecord?->pagu_porsi_besar ?? $paguSekarang->pagu_porsi_besar;

        $summaryHarian = $dataDistribusiRaw->groupBy('tanggal_harian')->map(function ($items) {
            return [
                'total_porsi_kecil' => $items->sum('porsi_kecil_harian'),
                'total_porsi_besar' => $items->sum('porsi_besar_harian'),
                'pagu_harian'       => $items->sum('pagu_harian_sekolah'),
            ];
        })->toArray();

        // Hari List + Preview
        $hariList = [];
        $previewHarian = [];
        $start = \Carbon\Carbon::parse($distribusi->tanggal_awal);
        $end = \Carbon\Carbon::parse($distribusi->tanggal_akhir);

        while ($start->lte($end)) {
            $tanggalStr = $start->format('Y-m-d');
            $hariList[] = $tanggalStr;

            if (isset($dataDistribusi[$tanggalStr])) {
                // Hari yang sudah ada datanya → gunakan data tersimpan (frozen)
                $totalKecil = $dataDistribusi[$tanggalStr]->sum('porsi_kecil_harian');
                $totalBesar = $dataDistribusi[$tanggalStr]->sum('porsi_besar_harian');
            } else {
                // Hari baru (belum pernah disimpan) → gunakan sekolah aktif SAAT INI
                $totalKecil = $sekolahAktifSekarang->sum('porsi_kecil_default');
                $totalBesar = $sekolahAktifSekarang->sum('porsi_besar_default');
            }
            
            $paguHarian = ($totalKecil * $paguKecilEfektif) + ($totalBesar * $paguBesarEfektif);

            $previewHarian[$tanggalStr] = [
                'total_porsi_kecil' => $totalKecil,
                'total_porsi_besar' => $totalBesar,
                'pagu_harian'       => $paguHarian,
            ];

            $start->addDay();
        }

        $grandTotalPagu = collect($summaryHarian)->sum('pagu_harian');

        return view('admin.distribusi.tindak-lanjut', compact(
            'distribusi',
            'sekolahAktif',
            'dataDistribusi',
            'hariList',
            'summaryHarian',
            'previewHarian',
            'paguKecilEfektif',
            'paguBesarEfektif',
            'grandTotalPagu'
        ));
    }
    
    public function simpanTotal(Request $request)
    {
        Log::debug('simpanTotal called', ['request' => $request->all()]);

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
                foreach ($dataTanggal as $tanggal_harian => $data) {
                    $existing = DistribusiSekolah::where([
                        'id_distribusi' => $id_distribusi,
                        'id_sekolah' => $id_sekolah,
                        'tanggal_harian' => $tanggal_harian,
                    ])->first();

                    $porsiKecil = (int) ($data['porsi_kecil_harian'] ?? ($existing?->porsi_kecil_harian ?? 0));
                    $porsiBesar = (int) ($data['porsi_besar_harian'] ?? ($existing?->porsi_besar_harian ?? 0));
                    $totalPenerima = $porsiKecil + $porsiBesar;

                    $jenisMenu = $data['jenis_menu'] ?? ($existing?->menu_kering > 0 ? 'kering' : 'basah');

                    // === SNAPSHOT PAGU ===
                    $paguKecil = $pagu->pagu_porsi_kecil;
                    $paguBesar = $pagu->pagu_porsi_besar;
                    $paguHarianSekolah = ($porsiKecil * $paguKecil) + ($porsiBesar * $paguBesar);

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
                            'pagu_porsi_kecil' => $paguKecil,
                            'pagu_porsi_besar' => $paguBesar,
                            'keterangan' => $data['keterangan'] ?? $existing?->keterangan,
                        ]
                    );

                    $saved++;
                }
            }

            Log::debug('Loop selesai, total saved', ['saved' => $saved]);

            return redirect()->route('admin.distribusi.index')
                ->with('success', "Berhasil menyimpan $saved record distribusi sekolah.");
        } catch (\Exception $e) {
            Log::error('Error di simpanTotal', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }

    // Detail
    public function detail($id)
    {
        $distribusi = Distribusi::findOrFail($id);

        // Status display berdasarkan distribusi_sekolah
        $totalRecords = DistribusiSekolah::where('id_distribusi', $distribusi->id)->count();
        $selesai = DistribusiSekolah::where('id_distribusi', $distribusi->id)
                    ->where('status', 'selesai')->count();
        $dikirim = DistribusiSekolah::where('id_distribusi', $distribusi->id)
                    ->where('status', 'dikirim')->count();

        if ($totalRecords == 0) {
            $distribusi->status_display = 'Draf';
            $distribusi->status_color   = 'bg-primary';
        } elseif ($selesai == $totalRecords) {
            $distribusi->status_display = 'Selesai';
            $distribusi->status_color   = 'bg-success';
        } elseif ($dikirim > 0 || $selesai > 0) {
            $distribusi->status_display = 'Diproses';
            $distribusi->status_color   = 'bg-warning text-dark';
        } else {
            $distribusi->status_display = 'Draf';
            $distribusi->status_color   = 'bg-primary';
        }
        
        $idSekolahTersimpan = DistribusiSekolah::where('id_distribusi', $id)
            ->distinct()->pluck('id_sekolah');

        $sekolahAktif = Sekolah::aktif()->get();
        $sekolahNonaktifTersimpan = Sekolah::whereIn('id', $idSekolahTersimpan)
            ->where('status', '!=', 'Aktif')->get();
        $sekolahAktif = $sekolahAktif->merge($sekolahNonaktifTersimpan)->unique('id');

        $pagu = Pagu::getPaguAktif();

        $start = \Carbon\Carbon::parse($distribusi->tanggal_awal);
        $end   = \Carbon\Carbon::parse($distribusi->tanggal_akhir);
        $hariList = [];
        $current = $start->clone();
        while ($current->lte($end)) {
            $hariList[] = $current->format('Y-m-d');
            $current->addDay();
        }

        $dataDistribusiRaw = DistribusiSekolah::where('id_distribusi', $id)
            ->with('sekolah')
            ->get();

        $dataDistribusi = $dataDistribusiRaw->groupBy('tanggal_harian')
            ->map(fn($items) => $items->keyBy('id_sekolah'));

        // Gunakan pagu_harian_sekolah yang sudah tersimpan, BUKAN hitung ulang
        $summaryHarian = $dataDistribusiRaw->groupBy('tanggal_harian')->map(function ($items) {
            return [
                'total_porsi_kecil' => $items->sum('porsi_kecil_harian'),
                'total_porsi_besar' => $items->sum('porsi_besar_harian'),
                'pagu_harian'       => $items->sum('pagu_harian_sekolah'),
            ];
        })->toArray();

        $grandTotalPagu = collect($summaryHarian)->sum('pagu_harian');

        return view('admin.distribusi.detail-distribusi', compact(
            'distribusi',
            'sekolahAktif',
            'dataDistribusi',
            'dataDistribusiRaw',
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


