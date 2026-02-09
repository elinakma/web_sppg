<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MapController extends Controller
{
    // menampilkan halaman map admin berupa peta dengan lokasi terakhir semua driver
    public function index()
    {
        $drivers = User::where('role', 'Driver')
                       ->with(['locations' => function ($query) {
                           $query->latest()->limit(1); // Hanya ambil lokasi terbaru
                       }])
                       ->get();

        return view('admin.map', compact('drivers'));
    }
}
