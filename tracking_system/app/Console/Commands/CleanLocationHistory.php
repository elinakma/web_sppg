<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LocationHistory;
use Illuminate\Support\Carbon;

class CleanLocationHistory extends Command
{
    protected $signature   = 'tracking:clean-history {--days=1 : Hapus history lebih dari N hari lalu}';
    protected $description = 'Hapus location_histories yang sudah lewat hari pengiriman (default: kemarin ke belakang)';
 
    public function handle(): int
    {
        $days = (int) $this->option('days');
 
        $cutoff = Carbon::today()->subDays($days);
 
        $deleted = LocationHistory::where('tanggal_pengiriman', '<', $cutoff)->delete();
 
        $this->info("Berhasil menghapus {$deleted} baris history sebelum {$cutoff->toDateString()}.");
 
        return self::SUCCESS;
    }
}
