<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Hapus history perjalanan kemarin setiap jam 00:10 ───────────────────────
Schedule::command('tracking:clean-history')->dailyAt('00:10');