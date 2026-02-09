<?php

namespace App\Http\Controllers;

use App\Models\Pagu;
use Illuminate\Http\Request;

class PaguController extends Controller
{
    public function index()
    {
        $pagu = Pagu::first(); // Ambil record pertama (global)
        return view('admin.pagu.kelola-pagu', compact('pagu'));
    }

    public function update(Request $request, Pagu $pagu)
    {
        $request->validate([
            'pagu_porsi_kecil' => 'required|integer|min:0',
            'pagu_porsi_besar' => 'required|integer|min:0',
        ]);

        $pagu->update($request->only(['pagu_porsi_kecil', 'pagu_porsi_besar']));

        return redirect()->route('admin.pagu.index')
            ->with('success', 'Pagu berhasil diperbarui.');
    }
}
