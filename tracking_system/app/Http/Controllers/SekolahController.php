<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index()
    {
        $sekolah = Sekolah::orderBy('nama_sekolah')->get();
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
            'porsi_kecil_default' => 'required|integer|min:0',
            'porsi_besar_default' => 'required|integer|min:0',
        ]);

        Sekolah::create($request->all());

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        return view('admin.sekolah.edit-sekolah', compact('sekolah'));
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $request->validate([
        'nama_sekolah'       => 'required|string|max:100|unique:sekolah,nama_sekolah,' . $sekolah->id,
        'pic'                   => 'required|string|max:100',
        'status'                => 'required|in:Aktif,Nonaktif',
        'porsi_kecil_default'   => 'required|integer|min:0',
        'porsi_besar_default'   => 'required|integer|min:0',
    ]);

    $sekolah->update($request->all());

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }
}