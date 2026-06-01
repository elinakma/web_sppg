<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\DistribusiSekolah;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
        $search = request()->search;

        // default sorting
        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $sekolah = Sekolah::when($search, function ($query, $search) {
                $query->where('nama_sekolah', 'like', "%{$search}%")
                      ->orWhere('pic', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate(8)
            ->withQueryString();
            
        return view('admin.sekolah.kelola-sekolah', compact('sekolah'));
    }

    public function create()
    {
        return view('admin.sekolah.tambah-sekolah');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:100|unique:sekolah,nama_sekolah',
            'pic'          => 'required|string|max:100',
            'status'       => 'required|in:Aktif,Nonaktif',
            'porsi_kecil_default' => 'nullable|integer|min:0',
            'porsi_besar_default' => 'nullable|integer|min:0',
        ]);

        Sekolah::create($request->all());

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $request->validate([
        'nama_sekolah'       => 'required|string|max:100|unique:sekolah,nama_sekolah,' . $sekolah->id,
        'pic'                   => 'required|string|max:100',
        'status'                => 'required|in:Aktif,Nonaktif',
        'porsi_kecil_default'   => 'nullable|integer|min:0',
        'porsi_besar_default'   => 'nullable|integer|min:0',
    ]);

    $sekolah->update($request->all());

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        // Cek apakah sekolah ini pernah dipakai di distribusi sekolah
        $pernahDipakai = DistribusiSekolah::where('id_sekolah', $sekolah->id)->exists();

        if ($pernahDipakai) {
            $sekolah->delete();
            return redirect()->route('admin.sekolah.index')
                ->with('success', 'Sekolah berhasil dihapus. Data historis di distribusi tetap tersimpan.');
        }

        // Jika belum pernah dipakai, hapus permanen
        $sekolah->forceDelete();

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus secara permanen.');
    }
}