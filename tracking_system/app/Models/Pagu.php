<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagu extends Model
{
    protected $table = 'pagu';

    protected $fillable = [
        'id',
        'pagu_porsi_kecil',
        'pagu_porsi_besar',
        'created_at',
        'updated_at'
    ];

    // Ambil pagu aktif (record pertama)
    public static function getPaguAktif()
    {
        return self::first() ?? self::create([
            'pagu_porsi_kecil' => 8000,
            'pagu_porsi_besar' => 10000,
        ]);
    }
}