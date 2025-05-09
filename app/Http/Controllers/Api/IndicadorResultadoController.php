<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndicadorConsolidado;
use App\Models\Encuesta;
use App\Models\Retroalimentacion;
use App\Models\EvaluaProveedores;
use App\Models\ResultadoIndi;
use App\Models\Registros;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class IndicadorResultadoController extends Controller
{
    /**
     * Guarda los resultados de un indicador consolidado en su tabla correspondiente.
     */
    public function store(Request $request, $idIndicador)
    {
        try {
            Log::info("📌 Datos recibidos en backend:", ['request' => $request->all()]);

            if (!is_numeric($idIndicador)) {
                Log::error("❌ ID de indicador no válido", ['idIndicador' => $idIndicador]);
                return response()->json(['message' => 'ID de indicador no válido'], 400);
            }

            $data = $request->get('result') ?? [];  // Evita error si result es null
            Log::info("📌 Datos procesados en backend:", ['data' => $data]);

            $indicador = IndicadorConsolidado::find($idIndicador);
            if (!$indicador) {
                Log::error("❌ Indicador no encontrado", ['idIndicador' => $idIndicador]);
                return response()->json(['message' => 'Indicador no encontrado'], 404);
            }

            $origen = $indicador->origenIndicador;
            Log::info("📌 Procesando resultado para origen: " . $origen);

            switch ($origen) {
                case 'Encuesta':
                    $resultado = Encuesta::updateOrCreate(
                        ['idIndicador' => $idIndicador],
                        [
                            'malo' => intval($data['malo'] ?? null),
                            'regular' => intval($data['regular'] ?? null),
                            'bueno' => intval($data['bueno'] ?? null),
                            'excelente' => intval($data['excelente'] ?? null),
                            'noEncuestas' => intval($data['noEncuestas'] ?? null),
                        ]
                    );
                    break;

                case 'Retroalimentacion':
                    $resultado = Retroalimentacion::updateOrCreate(
                        ['idIndicador' => $idIndicador],
                        [
                            'cantidadFelicitacion' => intval($data['cantidadFelicitacion'] ?? null),
                            'cantidadSugerencia' => intval($data['cantidadSugerencia'] ?? null),
                            'cantidadQueja' => intval($data['cantidadQueja'] ?? null),
                        ]
                    );
                    break;

                case 'EvaluaProveedores':
                    $resultado = EvaluaProveedores::updateOrCreate(
                        ['idIndicador' => $idIndicador],
                        [
                            'resultadoConfiableSem1' => intval($data['confiableSem1'] ?? null),
                            'resultadoConfiableSem2' => intval($data['confiableSem2'] ?? null),
                            'resultadoCondicionadoSem1' => intval($data['condicionadoSem1'] ?? null),
                            'resultadoCondicionadoSem2' => intval($data['condicionadoSem2'] ?? null),
                            'resultadoNoConfiableSem1' => intval($data['noConfiableSem1'] ?? null),
                            'resultadoNoConfiableSem2' => intval($data['noConfiableSem2'] ?? null),
                        ]
                    );
                    break;

                case 'ActividadControl':
                case 'MapaProceso':
                case 'GestionRiesgo':
                    $resultado = ResultadoIndi::updateOrCreate(
                        ['idIndicador' => $idIndicador],
                        [
                            'resultadoAnual' => isset($data['resultadoAnual']) ? intval($data['resultadoAnual']) : null,
                            'resultadoSemestral1' => isset($data['resultadoSemestral1']) ? intval($data['resultadoSemestral1']) : null,
                            'resultadoSemestral2' => isset($data['resultadoSemestral2']) ? intval($data['resultadoSemestral2']) : null,
                        ]
                    );
                    break;

                default:
                    Log::error("❌ Origen del indicador desconocido", ['origen' => $origen]);
                    return response()->json(['message' => 'Origen del indicador desconocido'], 400);
            }

            Log::info("✅ Resultado guardado con éxito", ['idIndicador' => $idIndicador, 'resultado' => $resultado]);

            return response()->json([
                'message' => 'Resultado registrado exitosamente',
                'resultado' => $resultado
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Error al registrar resultado", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al registrar el resultado'], 500);
        }
    }

    /**
     * Muestra los resultados de un indicador en su tabla correspondiente.
     */
    public function show($idIndicador)
    {
        try {
            $indicador = IndicadorConsolidado::find($idIndicador);

            if (!$indicador) {
                Log::warning("❌ Indicador no encontrado", ['id' => $idIndicador]);
                return response()->json(['message' => 'Indicador no encontrado'], 404);
            }

            $origen = $indicador->origenIndicador;
            $resultado = null;

            switch ($origen) {
                case 'Encuesta':
                    $resultado = Encuesta::where('idIndicador', $idIndicador)->first();
                    break;

                case 'Retroalimentacion':
                    $resultado = Retroalimentacion::where('idIndicador', $idIndicador)->first();
                    break;

                case 'EvaluaProveedores':
                    $resultado = EvaluaProveedores::where('idIndicador', $idIndicador)->first();
                    break;

                case 'ActividadControl':
                case 'MapaProceso':
                case 'GestionRiesgo':
                    $resultado = ResultadoIndi::where('idIndicador', $idIndicador)->first();
                    break;

                default:
                    Log::warning("❌ Tipo de indicador desconocido", ['origenIndicador' => $origen]);
                    return response()->json(['message' => 'Tipo de indicador no reconocido'], 400);
            }

            if (!$resultado) {
                Log::warning("❌ No se encontraron resultados", ['idIndicador' => $idIndicador]);
                return response()->json(['message' => 'No se encontraron resultados para este indicador'], 404);
            }

            Log::info("✅ Resultados obtenidos", [
                'idIndicador' => $idIndicador,
                'resultado' => $resultado
            ]);

            return response()->json(['resultado' => $resultado], 200);

        } catch (\Exception $e) {
            Log::error("❌ Error al obtener los resultados", [
                'idIndicador' => $idIndicador,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error al obtener los resultados'], 500);
        }
    }

    public function getResultadosPlanControl($idProceso)
{
    //ReVISAR POR QUE SE TENÍA LO DE INTERPRETACIÓN
    try {
        $resultados = DB::table('IndicadoresConsolidados as ic')
            ->join('ResultadoIndi as ri', 'ic.idIndicador', '=', 'ri.idIndicador')
            ->join('Registros as r', 'ic.idRegistro', '=', 'r.idRegistro')
            ->join('analisisdatos as ad', 'r.idRegistro', '=', 'ad.idRegistro')
            ->where('ic.origenIndicador', 'ActividadControl')
            ->where('ic.idProceso', $idProceso)
            // ->where('ad.seccion', 'DesempeñoProceso')
            ->select([
                'ic.nombreIndicador',
                'ri.resultadoSemestral1',
                'ri.resultadoSemestral2',
                // 'ad.interpretacion',
                // 'ad.necesidad'
            ])
            ->get();

        return response()->json($resultados, 200);
    } catch (\Exception $e) {
        Log::error("Error al obtener los resultados de Plan de Control", ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Error al obtener los resultados de Plan de Control'], 500);
    }
}


    public function getResultadosIndMapaProceso(Request $request)
    {
        $idProceso = $request->query('idProceso');
        if (!is_numeric($idProceso)) {
            return response()->json(['message' => 'Parámetro idProceso inválido'], 400);
        }

        $resultados = DB::table('IndicadoresConsolidados AS ic')
            ->join('ResultadoIndi            AS ri', 'ic.idIndicador', '=', 'ri.idIndicador')
            ->where('ic.origenIndicador', 'MapaProceso')
            ->where('ic.idProceso', $idProceso)
            ->select([
                'ic.nombreIndicador',
                'ri.resultadoSemestral1',
                'ri.resultadoSemestral2',
            ])
            ->get();

        return response()->json($resultados, 200);
    }

    public function getResultadosRiesgos($idRegistro)
    {
        // 1️⃣ Obtener idProceso desde Registros
        $registro = Registros::select('idProceso')->find($idRegistro);
        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
        $idProceso = $registro->idProceso;

        // 2️⃣ Un único query: indicadores + resultados
        $resultados = DB::table('IndicadoresConsolidados AS ic')
            ->join('ResultadoIndi            AS ri', 'ic.idIndicador', '=', 'ri.idIndicador')
            ->where('ic.origenIndicador', 'GestionRiesgo')
            ->where('ic.idProceso', $idProceso)
            ->select([
                'ic.nombreIndicador',
                'ri.resultadoAnual',
            ])
            ->get();

        return response()->json($resultados, 200);
    }

}
