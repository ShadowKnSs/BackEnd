<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registros;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\SeguimientoMinuta;
class SeguimientoSemController extends Controller
{
    public function obtenerDatosSeguimiento(Request $request)
    {
        $anio = $request->input('anio');
        $periodo = $request->input('periodo'); // Ejemplo: "01-06" o "07-12"

        // Convertir el periodo en rango de meses
        [$mesInicio, $mesFin] = explode("-", $periodo);
        $mesInicio = (int) $mesInicio;
        $mesFin = (int) $mesFin;

        \Log::info("Año recibido: $anio, Periodo recibido: $periodo");
        \Log::info("Mes Inicio: $mesInicio, Mes Fin: $mesFin");

        // 1️⃣ Obtener registros del año y con Apartado = "Seguimiento"
        $registros = Registros::where('año', $anio)
            ->where('Apartado', 'Seguimiento')
            ->get(['idRegistro', 'idProceso']);

        \Log::info("Registros obtenidos:", $registros->toArray());

        if ($registros->isEmpty()) {
            return response()->json([], 200);
        }

        // Extraer los idRegistro
        $idRegistros = $registros->pluck('idRegistro')->toArray();
        \Log::info("ID de Registros: ", $idRegistros);

        // 2️⃣ Filtrar seguimientoMinuta por idRegistro y por mes en el periodo dado
        $seguimientos = SeguimientoMinuta::whereIn('idRegistro', $idRegistros)
            ->whereMonth('fecha', '>=', $mesInicio)
            ->whereMonth('fecha', '<=', $mesFin)
            ->get(['idSeguimiento', 'idRegistro', 'fecha']);

        \Log::info("Seguimientos obtenidos:", $seguimientos->toArray());

        if ($seguimientos->isEmpty()) {
            return response()->json([], 200);
        }

        // Extraer los idRegistro válidos después del filtrado
        $idRegistrosValidos = $seguimientos->pluck('idRegistro')->toArray();
        \Log::info("ID de Registros válidos: ", $idRegistrosValidos);

        // 3️⃣ Obtener los procesos correspondientes a los registros válidos
        $procesos = Proceso::whereIn('idProceso', $registros->pluck('idProceso'))
            ->get(['idProceso', 'nombreProceso', 'idEntidad']);

        \Log::info("Procesos obtenidos:", $procesos->toArray());

        // 4️⃣ Obtener entidades de los procesos
        $idEntidades = $procesos->pluck('idEntidad')->toArray();
        $entidades = EntidadDependencia::whereIn('idEntidadDependecia', $idEntidades)
            ->get(['idEntidadDependecia', 'nombreEntidad']);

        \Log::info("Entidades obtenidas:", $entidades->toArray());

        // 5️⃣ Formar la respuesta final
        $resultado = [];

        foreach ($seguimientos as $seguimiento) {
            $registro = $registros->firstWhere('idRegistro', $seguimiento->idRegistro);
            $proceso = $procesos->firstWhere('idProceso', $registro->idProceso ?? null);
            $entidad = $entidades->firstWhere('idEntidadDependecia', $proceso->idEntidad ?? null);

            $resultado[] = [
                "NombreProceso" => $proceso->nombreProceso ?? null,
                "Entidad" => $entidad->nombreEntidad ?? null,
                "fecha" => $seguimiento->fecha,
            ];
        }

        \Log::info("Resultado Final:", $resultado);

        return response()->json($resultado, 200);
    }
}
