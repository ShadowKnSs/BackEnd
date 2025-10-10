<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\FinalizarAuditoriasVencidas;
use App\Console\Commands\PurgeAuditoriasAntiguas;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(FinalizarAuditoriasVencidas::class)
    ->everyTenMinutes()   
    ->withoutOverlapping()
    ->onOneServer(); 

Schedule::command('tokens:delete-expired')->weeklyOn(0, '00:00');


Schedule::command(PurgeAuditoriasAntiguas::class, ['--months=3'])
    ->dailyAt('02:15')  
    ->withoutOverlapping()
    ->onOneServer();