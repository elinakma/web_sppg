<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribusi extends Model
{
    protected $table = 'distribusi';

    protected $fillable = [
        'tanggal_distribusi',
        'status'
    ];

    protected $casts = [
        'tanggal_distribusi' => 'date',
    ];
}
