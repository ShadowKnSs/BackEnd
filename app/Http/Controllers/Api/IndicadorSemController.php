<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registros;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\IndicadorConsolidado;
use App\Models\ResultadoIndi;
use App\Models\EvaluaProveedores;

class IndicadorSemController extends Controller
{
    public function obtenerDatosIndicadores(Request $request)
    {
        $anio = $request->input('anio');
        $periodo = $request->input('periodo'); // "01-06" o "07-12"
        $semestre = $periodo == "01-06" ? "resultadoSemestral1" : "resultadoSemestral2";
        $semestreEvalua = $periodo == "01-06" 
            ? ['resultadoConfiableSem1', 'resultadoCondicionadoSem1', 'resultadoNoConfiableSem1']
            : ['resultadoConfiableSem2', 'resultadoCondicionadoSem2', 'resultadoNoConfiableSem2'];

        // 1. Obtener los registros del apartado "Indicadores"
        $registros = Registros::where('año', $anio)
            ->where('Apartado', 'Indicadores')
            ->get(['idRegistro', 'idProceso']);

        if ($registros->isEmpty()) {
            return response()->json([]);
        }

        $idRegistros = $registros->pluck('idRegistro');

        // 2. Buscar indicadores con periodicidad "Semestral"
        $indicadores = IndicadorConsolidado::whereIn('idRegistro', $idRegistros)
            ->where('periodicidad', 'Semestral')
            ->get(['idIndicador', 'idRegistro', 'nombreIndicador', 'origenIndicador']);

        if ($indicadores->isEmpty()) {
            return response()->json([]);
        }

        $idIndicadores = $indicadores->pluck('idIndicador');

        // 3. Obtener resultados de ResultadoIndi
        $resultadosIndi = ResultadoIndi::whereIn('idIndicador', $idIndicadores)
            ->get(['idIndicador', $semestre]);

        // 4. Obtener resultados de EvaluaProveedores
        $resultadosEvalua = EvaluaProveedores::whereIn('idIndicador', $idIndicadores)
            ->get(array_merge(['idIndicador'], $semestreEvalua));

        // 5. Filtrar indicadores con datos completos
        $resultados = [];
        foreach ($indicadores as $indicador) {
            $resultado = $resultadosIndi->where('idIndicador', $indicador->idIndicador)->first();

            if ($resultado && !is_null($resultado->$semestre)) {
                $valorResultado = $resultado->$semestre;
            } else {
                $resultadoEvalua = $resultadosEvalua->where('idIndicador', $indicador->idIndicador)->first();
                if ($resultadoEvalua && !in_array(null, array_values($resultadoEvalua->only($semestreEvalua)), true)) {
                    $valorResultado = collect($resultadoEvalua->only($semestreEvalua))->avg();
                } else {
                    continue; // Omitir este indicador si no tiene datos completos
                }
            }

            $proceso = Proceso::where('idProceso', $registros->where('idRegistro', $indicador->idRegistro)->first()->idProceso)
                ->first(['idEntidad', 'nombreProceso']);

            $entidad = EntidadDependencia::where('idEntidadDependecia', $proceso->idEntidad)
                ->first(['nombreEntidad']);

            $resultados[] = [
                'NombreProceso' => $proceso->nombreProceso,
                'Entidad' => $entidad->nombreEntidad,
                'nombreIndicador' => $indicador->nombreIndicador,
                'origenIndicador' => $indicador->origenIndicador,
                'resultado' => $valorResultado,
            ];
        }

        return response()->json($resultados);
    }

}
