<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = Notifikasi::untukUser(Auth::id())
            ->with('pengirim')
            ->latest()
            ->paginate(20);

        // Tandai semua belum dibaca → dibaca saat halaman dibuka
        Notifikasi::untukUser(Auth::id())
            ->belumDibaca()
            ->update(['dibaca' => true, 'waktu_dibaca' => now()]);

        return view('admin.notifikasi.index', compact('notifikasi'));
    }

    public function dropdown()
    {
        $notifikasi = Notifikasi::untukUser(Auth::id())
            ->with('pengirim')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'judul'    => $n->judul,
                'pesan'    => $n->pesan,
                'is_read'  => $n->dibaca,
                'waktu'    => $n->waktuRelatif(),
                'url'      => $n->url ?? '#',
                'tipe'     => $n->tipe,
                'pengirim' => $n->pengirim?->name ?? '-',
            ]);

        $belumDibaca = Notifikasi::untukUser(Auth::id())
            ->belumDibaca()
            ->count();

        return response()->json([
            'notifikasi'   => $notifikasi,
            'belum_dibaca' => $belumDibaca,
        ]);
    }

    public function baca(Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $notifikasi->tandaiDibaca();
        return response()->json(['success' => true]);
    }

    public function bacaSemua()
    {
        Notifikasi::untukUser(Auth::id())
            ->belumDibaca()
            ->update(['dibaca' => true, 'waktu_dibaca' => now()]);
        return response()->json(['success' => true]);
    }
}
