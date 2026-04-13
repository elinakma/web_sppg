<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sekolah;
use App\Models\DistribusiSekolah;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahPengguna   = User::count();
        $jumlahSekolah    = Sekolah::aktif()->count();
        $distribusiHariIni = DistribusiSekolah::whereDate('tanggal_harian', now()->toDateString())->count();

        return view('admin.dashboard_admin', compact(
            'jumlahPengguna',
            'jumlahSekolah',
            'distribusiHariIni'
        ));
    }
}