<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('lofbi:hitung-penyusutan --tanggal='.now()->toDateString())
    ->yearlyOn(1, 1, '00:05');

Schedule::command('lofbi:hitung-penyusutan --tanggal='.now()->toDateString())
    ->yearlyOn(7, 1, '00:05');
