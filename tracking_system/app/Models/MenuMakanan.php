<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuMakanan extends Model
{
    protected $table = 'menu_makanan';

    protected $fillable = [
        'tanggal_menu',
        'jenis_porsi',
        'nama_menu',
    ];

    protected $casts = [
        'tanggal_menu' => 'date',
    ];

    public function bahan()
    {
        return $this->hasMany(BahanMakanan::class, 'menu_makanan_id');
    }

    public function akg()
    {
        return $this->hasOne(AkgMenu::class, 'menu_makanan_id');
    }
}