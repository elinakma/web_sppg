<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /** GET /admin/notifikasi — halaman daftar semua notifikasi */
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

    /** GET /admin/notifikasi/dropdown — AJAX, 10 notif terbaru */
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
                'is_read'  => $n->dibaca,       // ← tetap kirim is_read ke JS navbar
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

    /** POST /admin/notifikasi/{notifikasi}/baca */
    public function baca(Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $notifikasi->tandaiDibaca();
        return response()->json(['success' => true]);
    }

    /** POST /admin/notifikasi/baca-semua */
    public function bacaSemua()
    {
        Notifikasi::untukUser(Auth::id())
            ->belumDibaca()
            ->update(['dibaca' => true, 'waktu_dibaca' => now()]);
        return response()->json(['success' => true]);
    }

    /** DELETE /admin/notifikasi/{notifikasi} */
    public function destroy(Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $notifikasi->delete();
        return response()->json(['success' => true]);
    }

    /** DELETE /admin/notifikasi/hapus-semua */
    public function hapusSemua()
    {
        Notifikasi::untukUser(Auth::id())->delete();
        return response()->json(['success' => true]);
    }
}
