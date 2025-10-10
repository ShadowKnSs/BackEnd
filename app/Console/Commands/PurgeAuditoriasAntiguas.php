<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;
use App\Notifications\AuditoriaNotificacion;
use Carbon\Carbon;

class PurgeAuditoriasAntiguas extends Command
{
    protected $signature = 'auditorias:purge-antiguas
                            {--months=3 : Meses de retención}
                            {--batch=500 : Tamaño de lote}
                            {--dry-run : Solo contar, sin borrar}';

    protected $description = 'Elimina auditorías Finalizadas/Canceladas con fecha programada anterior al umbral de retención y limpia sus notificaciones.';

    public function handle(): int
    {
        $months = (int) ($this->option('months') ?? 3);
        $batch  = (int) ($this->option('batch') ?? 500);
        $dry    = (bool) $this->option('dry-run');

        // Umbral: ahora - N meses (sin overflow) y al inicio del día
        $cutoff = Carbon::now()->subMonthsNoOverflow($months)->startOfDay();

        // IDs candidatos (solo estados cerrados)
        $ids = DB::table('auditorias')
            ->whereIn('estado', ['Finalizada', 'Cancelada'])
            ->whereDate('fechaProgramada', '<', $cutoff->toDateString())
            ->pluck('idAuditoria');

        $total = $ids->count();

        $this->info("Candidatas a purgar (< {$cutoff->toDateString()}): {$total}");

        if ($dry || $total === 0) {
            return self::SUCCESS;
        }

        // Procesar en lotes
        $deletedAud = 0;
        $deletedNot = 0;

        foreach ($ids->chunk($batch) as $chunk) {
            $idList = $chunk->values()->all();

            // 1) Borrar notificaciones en BD relacionadas (dos rutas por compatibilidad)
            $deletedNot += DatabaseNotification::query()
                ->where('type', AuditoriaNotificacion::class)
                ->where(function ($q) use ($idList) {
                    $q->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.idAuditoria'))"), $idList)
                      ->orWhereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.data.idAuditoria'))"), $idList);
                })
                ->delete();

            // 2) Borrar auditorías (auditoresasignados cae por FK ON DELETE CASCADE)
            $deletedAud += DB::table('auditorias')
                ->whereIn('idAuditoria', $idList)
                ->delete();
        }

        $this->info("Notificaciones eliminadas: {$deletedNot}");
        $this->info("Auditorías eliminadas: {$deletedAud}");

        return self::SUCCESS;
    }
}
