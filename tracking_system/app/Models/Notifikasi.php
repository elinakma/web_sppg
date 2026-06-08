<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'distribusi_sekolah_id',
        'pengirim_id',
        'judul',
        'pesan',
        'tipe',
        'url',
        'dibaca',
        'waktu_dibaca',
    ];

    protected $casts = [
        'dibaca'       => 'boolean',
        'waktu_dibaca' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function distribusiSekolah()
    {
        return $this->belongsTo(DistribusiSekolah::class, 'distribusi_sekolah_id');
    }


    public function scopeBelumDibaca($query)
    {
        return $query->where('dibaca', false);
    }

    public function scopeUntukUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function tandaiDibaca(): void
    {
        if (!$this->dibaca) {
            $this->update([
                'dibaca'       => true,
                'waktu_dibaca' => now(),
            ]);
        }
    }

    /**
     * Kirim notifikasi ke semua Admin DAN Aslap.
     * Dipanggil dari API route saat driver checklist/stop tracking.
     */
    public static function kirimKeAdminDanAslap(array $data): void
    {
        // Kirim ke Admin
        $penerima = User::whereIn('role', ['Admin', 'Aslap'])->get();

        foreach ($penerima as $user) {
            static::create([
                'user_id'               => $user->id,
                'distribusi_sekolah_id' => $data['distribusi_sekolah_id'] ?? null,
                'pengirim_id'           => $data['pengirim_id']           ?? null,
                'judul'                 => $data['judul'],
                'pesan'                 => $data['pesan'],
                'tipe'                  => $data['tipe'] ?? 'pengiriman_selesai',
                'url'                   => $data['url'] ?? null,
                'dibaca'                => false,
            ]);
        }
    }

    /**
     * Alias lama — tetap ada agar tidak perlu ubah semua route sekaligus.
     * Ke depan pakai kirimKeAdminDanAslap().
     */
    public static function kirimKeAdmin(array $data): void
    {
        static::kirimKeAdminDanAslap($data);
    }

    public function waktuRelatif(): string
    {
        $diff = abs(now()->diffInSeconds($this->created_at));
        
        if ($diff < 60)     return 'Baru saja';
        if ($diff < 3600)   return (int)($diff / 60)   . ' menit lalu';
        if ($diff < 86400)  return (int)($diff / 3600)  . ' jam lalu';
        if ($diff < 604800) return (int)($diff / 86400) . ' hari lalu';

        return $this->created_at->format('d M Y');
    }
}