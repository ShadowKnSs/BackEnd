<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registros;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\ActividadMejora;
use App\Models\PlanTrabajo;
use App\Models\FuentePt;
class ActMejoraSemController extends Controller
{
    public function obtenerDatosAccionesMejora(Request $request)
{
    $anio = $request->input('anio');
    $periodo = $request->input('periodo'); // Ejemplo: "01-06" o "07-12"

    [$mesInicio, $mesFin] = explode("-", $periodo);
    $mesInicio = (int) $mesInicio;
    $mesFin = (int) $mesFin;

    // 1️⃣ Obtener registros del año con Apartado = "Acciones de Mejora"
    $registros = Registros::where('año', $anio)
        ->where('Apartado', 'Acciones de Mejora')
        ->get(['idRegistro', 'idProceso']);

    if ($registros->isEmpty()) {
        return response()->json([], 200);
    }

    $idRegistros = $registros->pluck('idRegistro');

    // 2️⃣ Buscar en ActividadMejora los registros válidos
    $actividades = ActividadMejora::whereIn('idRegistro', $idRegistros)
        ->get(['idActividadMejora', 'idRegistro']);

    if ($actividades->isEmpty()) {
        return response()->json([], 200);
    }

    $idActividadMejoras = $actividades->pluck('idActividadMejora');
    $idRegistrosValidos = $actividades->pluck('idRegistro');

    // 3️⃣ Buscar en PlanTrabajo las actividades dentro del periodo
    $planTrabajos = PlanTrabajo::whereIn('idActividadMejora', $idActividadMejoras)
        ->where(function ($query) use ($mesInicio, $mesFin) {
            $query->whereMonth('fechaElaboracion', '>=', $mesInicio)
                  ->whereMonth('fechaElaboracion', '<=', $mesFin)
                  ->orWhere(function ($query) use ($mesInicio, $mesFin) {
                      $query->whereMonth('fechaRevision', '>=', $mesInicio)
                            ->whereMonth('fechaRevision', '<=', $mesFin);
                  });
        })
        ->get(['idPlanTrabajo', 'idActividadMejora', 'entregable', 'responsable']);

    if ($planTrabajos->isEmpty()) {
        return response()->json([], 200);
    }

    // 🔄 Obtener fuente y estado desde la tabla FuentePt
    $fuentes = FuentePt::whereIn('idPlanTrabajo', $planTrabajos->pluck('idPlanTrabajo'))
        ->get(['idPlanTrabajo', 'nombreFuente', 'estado']);

    $idActividadMejorasValidas = $planTrabajos->pluck('idActividadMejora');
    $idRegistrosFinales = $actividades->whereIn('idActividadMejora', $idActividadMejorasValidas)
                                      ->pluck('idRegistro');

    // 4️⃣ Obtener los procesos y entidades
    $procesos = Proceso::whereIn('idProceso', $registros->whereIn('idRegistro', $idRegistrosFinales)
        ->pluck('idProceso'))
        ->get(['idProceso', 'nombreProceso', 'idEntidad']);

    $entidades = EntidadDependencia::whereIn('idEntidadDependencia', $procesos->pluck('idEntidad'))
        ->get(['idEntidadDependencia', 'nombreEntidad']);

    // 5️⃣ Construir la respuesta
    $resultado = [];

    foreach ($planTrabajos as $plan) {
        $actividad = $actividades->firstWhere('idActividadMejora', $plan->idActividadMejora);
        $registro = $registros->firstWhere('idRegistro', $actividad->idRegistro ?? null);
        $proceso = $procesos->firstWhere('idProceso', $registro->idProceso ?? null);
        $entidad = $entidades->firstWhere('idEntidadDependencia', $proceso->idEntidad ?? null);

        // Buscar fuente y estado en FuentePt
        $fuente = $fuentes->firstWhere('idPlanTrabajo', $plan->idPlanTrabajo);

        $resultado[] = [
            "NombreProceso" => $proceso->nombreProceso ?? null,
            "Entidad" => $entidad->nombreEntidad ?? null,
            "fuente" => $fuente->nombreFuente ?? null,
            "entregable" => $plan->entregable,
            "responsable" => $plan->responsable,
            "estado" => $fuente->estado ?? null,
        ];
    }

    return response()->json($resultado, 200);
}

}
