<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnalisisDatos;
use App\Models\IndicadorConsolidado;
use App\Models\Encuesta;
use App\Models\Retroalimentacion;
use App\Models\EvaluaProveedores;
use Illuminate\Support\Facades\Log;

class IndicadorResultadoController extends Controller
{
    public function store(Request $request, $idIndicadorConsolidado)
    {
        try {
            // Obtiene el payload enviado en 'result'
            $data = $request->get('result');
            // Obtiene la periodicidad enviada o usa "Semestral" por defecto
            $indicadorPeriod = $request->get('periodicidad') ?? "Semestral"; 

            // Inicializa variables para los resultados
            if ($indicadorPeriod === 'Semestral') {
                $resultado1 = isset($data['Ene-Jun']['resultado']) && $data['Ene-Jun']['resultado'] !== ""
                    ? (int)$data['Ene-Jun']['resultado']
                    : null;
                $resultado2 = isset($data['Jul-Dic']['resultado']) && $data['Jul-Dic']['resultado'] !== ""
                    ? (int)$data['Jul-Dic']['resultado']
                    : null;
            } else {
                $resultado1 = isset($data['result']) && $data['result'] !== ""
                    ? (int)$data['result']
                    : null;
                $resultado2 = null;
            }

            // Crear o actualizar el registro en analisisdatos
            $analisis = AnalisisDatos::updateOrCreate(
                ['idIndicadorConsolidado' => $idIndicadorConsolidado],
                [
                    'resultadoSemestral1' => $resultado1,
                    'resultadoSemestral2' => $resultado2,
                ]
            );

            // Log para verificar que se creó el registro y tiene id
            Log::info("AnalisisDatos creado/actualizado", ['analisis' => $analisis]);

            // Verificamos que se obtuvo un id válido
            if (!$analisis->idAnalisisDatos) {
                Log::error("No se pudo obtener idAnalisisDatos para el indicador", ['idIndicadorConsolidado' => $idIndicadorConsolidado]);
                return response()->json([
                    'message' => 'Error al registrar el resultado: ID de análisis no obtenido'
                ], 500);
            }

            // Obtenemos el indicador consolidado para determinar el origen
            $indicador = IndicadorConsolidado::findOrFail($idIndicadorConsolidado);

            // Según el origenIndicador, actualizamos la tabla correspondiente
            switch ($indicador->origenIndicador) {
                case 'Encuesta':
                    $encuesta = Encuesta::updateOrCreate(
                        ['idIndicador' => $analisis->idAnalisisDatos],
                        [
                            'malo' => isset($data['malo']) ? (int)$data['malo'] : null,
                            'regular' => isset($data['regular']) ? (int)$data['regular'] : null,
                            'excelenteBueno' => isset($data['excelenteBueno']) ? (int)$data['excelenteBueno'] : null,
                            'noEncuestas' => isset($data['noEncuestas']) ? (int)$data['noEncuestas'] : null,
                        ]
                    );
                    Log::info("Encuesta registrada para indicador", ['idIndicadorConsolidado' => $idIndicadorConsolidado]);
                    break;
                case 'Retroalimentacion':
                    $retro = Retroalimentacion::updateOrCreate(
                        ['idIndicador' => $analisis->idAnalisisDatos],
                        [
                            'metodo' => $request->get('metodo'),
                            'cantidadFelicitacion' => isset($data['cantidadFelicitacion']) ? (int)$data['cantidadFelicitacion'] : null,
                            'cantidadSugerencia' => isset($data['cantidadSugerencia']) ? (int)$data['cantidadSugerencia'] : null,
                            'cantidadQueja' => isset($data['cantidadQueja']) ? (int)$data['cantidadQueja'] : null,
                        ]
                    );
                    Log::info("Retroalimentación registrada para indicador", ['idIndicadorConsolidado' => $idIndicadorConsolidado]);
                    break;
                case 'EvaluaProveedores':
                    $evalua = EvaluaProveedores::updateOrCreate(
                        ['idIndicador' => $analisis->idAnalisisDatos],
                        [
                            'confiable' => isset($data['confiable']) ? (int)$data['confiable'] : null,
                            'noConfiable' => isset($data['noConfiable']) ? (int)$data['noConfiable'] : null,
                            'condicionado' => isset($data['condicionado']) ? (int)$data['condicionado'] : null,
                        ]
                    );
                    Log::info("Evaluación de proveedores registrada para indicador", ['idIndicadorConsolidado' => $idIndicadorConsolidado]);
                    break;
                default:
                    // No se requiere acción extra para otros orígenes
                    break;
            }

            return response()->json([
                'message' => 'Resultado registrado exitosamente',
                'analisis' => $analisis
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al registrar resultado para indicador {$idIndicadorConsolidado}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar el resultado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($idIndicadorConsolidado)
    {
        $analisis = AnalisisDatos::where('idIndicadorConsolidado', $idIndicadorConsolidado)->first();
        return response()->json(['analisis' => $analisis], 200);
    }
}
