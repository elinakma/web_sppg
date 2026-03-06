<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
        })
        ->orderBy('name', 'asc')
        ->paginate(8)
        ->withQueryString();

        return view('pengguna.kelola-pengguna', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telepon'  => 'nullable|string|max:20',
            'role'     => 'required|in:Admin,Aslap,Gizi,Akuntan,Driver',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'telepon'  => $request->telepon,
            'status'   => 'Aktif',
            'role'     => $request->role,
        ]);

        return redirect()->route('admin.pengguna.index')
                         ->with('success', 'Data pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $pengguna->id,
            'password' => 'nullable|string|min:8|confirmed',
            'telepon' => 'nullable|string|regex:/^[89][0-9]{8,12}$/|max:20',
            'role'     => 'required|in:Admin,Aslap,Gizi,Akuntan,Driver',
        ]);

        $pengguna->name = $request->name;
        $pengguna->email = $request->email;
        $pengguna->telepon = $request->telepon;
        $pengguna->role = $request->role;

        if ($request->filled('password')) {
            $pengguna->password = Hash::make($request->password);
        }

        $pengguna->save();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        // Opsional: larang hapus akun Admin terakhir
        if ($pengguna->role === 'Admin' && User::where('role', 'Admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}