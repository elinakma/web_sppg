<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanMakanan extends Model
{
    protected $table = 'bahan_makanan';

    protected $fillable = [
        'menu_makanan_id',
        'nama_bahan',
        'jumlah',
        'satuan',
        'harga_satuan',
    ];

    public function menu()
    {
        return $this->belongsTo(MenuMakanan::class, 'menu_makanan_id');
    }
}