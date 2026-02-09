<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama_sekolah',
        'pic',
        'status',
        'porsi_kecil_default',
        'porsi_besar_default',
    ];

    protected $casts = [
        'status' => 'string',
        'porsi_kecil_default' => 'integer',
        'porsi_besar_default' => 'integer',
    ];

    // Opsional: scope untuk filter aktif saja
    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }
}