<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telepon',
        'status',
        'role',
    ];

    protected $attributes = [
        'status' => 'Aktif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    // Cek role
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }
    public function isAslap()
    {
        return $this->role === 'Aslap';
    }
    public function isGizi()
    {
        return $this->role === 'Gizi';
    }
    public function isAkuntan()
    {
        return $this->role === 'Akuntan';
    }
    public function isDriver()
    {
        return $this->role === 'Driver';
    }
    

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class, 'driver_id');
    }

    public function assignedSekolah()
    {
        return $this->belongsToMany(Sekolah::class, 'pengiriman', 'driver_id', 'sekolah_id')
                    ->withTimestamps();
    }
}
