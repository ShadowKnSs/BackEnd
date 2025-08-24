<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cronograma;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FinalizarAuditoriasVencidas extends Command
{
    protected $signature = 'auditorias:finalizar-vencidas';
    protected $description = 'Finaliza automáticamente auditorías pendientes si han pasado más de 3 horas de su programación.';

    public function handle()
    {
        $now = Carbon::now();

        $auditorias = Cronograma::where('estado', 'Pendiente')->get();

        foreach ($auditorias as $auditoria) {
            $programacion = Carbon::parse("{$auditoria->fechaProgramada} {$auditoria->horaProgramada}");

            // Verifica si han pasado al menos 3 horas desde la programación
            if ($now->diffInHours($programacion, false) <= -3) {
                $auditoria->estado = 'Finalizada';
                $auditoria->save();

                Log::info("🕒 Auditoría marcada como finalizada automáticamente", [
                    'id' => $auditoria->id,
                    'programacion' => $programacion->toDateTimeString(),
                    'ahora' => $now->toDateTimeString(),
                ]);
            }
        }

        $this->info('✔ Auditorías vencidas actualizadas correctamente.');
    }
}
