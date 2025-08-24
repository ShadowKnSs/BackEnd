<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\FinalizarAuditoriasVencidas;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(FinalizarAuditoriasVencidas::class)
    ->hourly();

Schedule::command('tokens:delete-expired')->weeklyOn(0, '00:00');

