<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinalizarAuditoriasVencidas extends Command
{
    protected $signature = 'auditorias:finalizar-vencidas {--dry-run : Muestra cuántas se actualizarían sin guardar}';
    protected $description = 'Marca como Finalizada toda auditoría Pendiente con >= 3 horas desde su hora programada.';

    public function handle(): int
    {
        // Usa la zona horaria de config/app.php
        $now = Carbon::now();
        $threshold = $now->copy()->subHours(3); // ahora - 3h

        // Cuenta candidatas
        $count = DB::table('auditorias as a')
            ->where('a.estado', 'Pendiente')
            ->whereRaw('TIMESTAMP(a.fechaProgramada, a.horaProgramada) <= ?', [$threshold->toDateTimeString()])
            ->count();

        if ($this->option('dry-run')) {
            $this->info("Candidatas a auto-finalizar: {$count}");
            return self::SUCCESS;
        }

        // Actualiza en bloque
        $affected = DB::table('auditorias as a')
            ->where('a.estado', 'Pendiente')
            ->whereRaw('TIMESTAMP(a.fechaProgramada, a.horaProgramada) <= ?', [$threshold->toDateTimeString()])
            ->update(['estado' => 'Finalizada']);

        Log::info('[FinalizarAuditoriasVencidas] Auditorías auto-finalizadas', [
            'affected' => $affected,
            'threshold' => $threshold->toDateTimeString(),
        ]);

        $this->info("Auditorías auto-finalizadas: {$affected}");
        return self::SUCCESS;
    }
}
