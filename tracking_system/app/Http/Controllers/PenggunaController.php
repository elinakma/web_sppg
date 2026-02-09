<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id')->get();
        return view('pengguna.kelola-pengguna', compact('users'));
    }

    public function create()
    {
        return view('pengguna.tambah-pengguna');
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
                         ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('pengguna.edit-pengguna', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'telepon' => 'nullable|string|regex:/^[89][0-9]{8,12}$/|max:20',
            'role'     => 'required|in:Admin,Aslap,Gizi,Akuntan,Driver',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->telepon = $request->telepon;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Opsional: larang hapus akun Admin terakhir
        if ($user->role === 'Admin' && User::where('role', 'Admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}