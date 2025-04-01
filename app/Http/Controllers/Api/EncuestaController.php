<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;
use App\Models\AnalisisDatos;
use Illuminate\Support\Facades\Log;

class EncuestaController extends Controller
{
    // Guardar/actualizar resultados => POST /api/encuesta/{idIndicadorConsolidado}/resultados
    public function store(Request $request, $idIndicadorConsolidado)
    {
        try {
            // 1. Buscar la fila en analisisdatos
            $analisisRegistro = AnalisisDatos::where('idIndicadorConsolidado', $idIndicadorConsolidado)->first();
            if (!$analisisRegistro) {
                Log::error("No se encontró registro en analisisdatos para idIndicadorConsolidado: {$idIndicadorConsolidado}");
                return response()->json([
                    'message' => 'No se encontró el registro en analisisdatos'
                ], 404);
            }

            // 2. Obtenemos el idIndicador real
            $realIdIndicador = $analisisRegistro->idIndicador;

            // 3. Obtenemos la data => "result"
            $data = $request->get('result');
            // Convertimos a enteros, asumiendo que pueden ser "" o nulos
            $malo = !empty($data['malo']) ? (int)$data['malo'] : 0;
            $regular = !empty($data['regular']) ? (int)$data['regular'] : 0;
            $bueno = !empty($data['bueno']) ? (int)$data['bueno'] : 0;
            $excelente = !empty($data['excelente']) ? (int)$data['excelente'] : 0;
            $noEncuestas = !empty($data['noEncuestas']) ? (int)$data['noEncuestas'] : 0;

            // 4. updateOrCreate en la tabla encuesta con idIndicador = $realIdIndicador
            $encuesta = Encuesta::updateOrCreate(
                ['idIndicador' => $realIdIndicador],
                [
                    'malo' => $malo,
                    'regular' => $regular,
                    'bueno' => $bueno,
                    'excelente' => $excelente,
                    'noEncuestas' => $noEncuestas,
                ]
            );

            Log::info("Encuesta registrada/actualizada para idConsolidado={$idIndicadorConsolidado} => idIndicador={$realIdIndicador}", [
                'encuesta' => $encuesta
            ]);

            return response()->json([
                'message' => 'Encuesta registrada exitosamente',
                'encuesta' => $encuesta
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al registrar encuesta para idIndicadorConsolidado {$idIndicadorConsolidado}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar la encuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener resultados => GET /api/encuesta/{idIndicadorConsolidado}/resultados
    public function show($idIndicadorConsolidado)
    {
        try {
            // 1. Buscar la fila en analisisdatos
            $analisisRegistro = AnalisisDatos::where('idIndicadorConsolidado', $idIndicadorConsolidado)->first();
            if (!$analisisRegistro) {
                Log::info("No se encontró registro en analisisdatos para idIndicadorConsolidado: {$idIndicadorConsolidado}");
                return response()->json([
                    'message' => 'No se encontró la encuesta (analisisdatos).'
                ], 404);
            }

            // 2. Obtenemos el idIndicador real
            $realIdIndicador = $analisisRegistro->idIndicador;

            // 3. Buscar la encuesta
            $encuesta = Encuesta::where('idIndicador', $realIdIndicador)->first();
            if (!$encuesta) {
                Log::info("No se encontró encuesta para idIndicador: {$realIdIndicador}");
                return response()->json([
                    'message' => 'No se encontró la encuesta.'
                ], 404);
            }

            return response()->json([
                'encuesta' => $encuesta
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al obtener la encuesta para idIndicadorConsolidado {$idIndicadorConsolidado}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la encuesta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
