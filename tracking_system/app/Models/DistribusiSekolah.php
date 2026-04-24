<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistribusiSekolah extends Model
{
    protected $table = 'distribusi_sekolah';

    protected $fillable = [
        'id_distribusi',
        'id_sekolah',
        'tanggal_harian',
        'porsi_kecil_harian',
        'porsi_besar_harian',
        'menu_kering',
        'menu_basah',
        'total_penerima',
        'pagu_harian_sekolah',
        'status',
        'pengirim',
        'waktu'
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'id_sekolah');
    }

    public function driverPengirim()
    {
        return $this->belongsTo(User::class, 'pengirim', 'id');
    }

    // public function getPaguSekolahAttribute()
    // {
    //     $pagu = Pagu::getPaguAktif();
    //     return ($this->porsi_kecil_harian * $pagu->pagu_porsi_kecil) +
    //            ($this->porsi_besar_harian * $pagu->pagu_porsi_besar);
    // }
}
