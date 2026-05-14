<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkgHarian extends Model
{
    protected $table = 'akg_harian';
    protected $primaryKey = 'tanggal_harian';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tanggal_harian',
        'energi_kecil', 'karbo_kecil', 'lemak_kecil', 'protein_kecil', 'serat_kecil',
        'energi_besar', 'karbo_besar', 'lemak_besar', 'protein_besar', 'serat_besar',
    ];

    protected $casts = [
        'tanggal_harian' => 'date',
    ];
}
