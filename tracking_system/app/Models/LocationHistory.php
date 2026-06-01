<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'tracked_at',
        'tanggal_pengiriman',
    ];
 
    protected $casts = [
        'tracked_at'          => 'datetime',
        'tanggal_pengiriman'  => 'date',
        'latitude'            => 'float',
        'longitude'           => 'float',
    ];
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
    /**
     * Scope: history hari ini
     */
    public function scopeToday($query)
    {
        return $query->where('tanggal_pengiriman', today());
    }
 
    /**
     * Scope: history untuk driver tertentu
     */
    public function scopeForDriver($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }   
}
