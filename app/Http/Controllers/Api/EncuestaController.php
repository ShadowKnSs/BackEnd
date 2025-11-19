<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;
use Illuminate\Support\Facades\Log;

class EncuestaController extends Controller
{
    /**
     * Almacena un nuevo resultado de encuesta en la base de datos.
     */
    public function store(Request $request, $idIndicador)
    {
        try {
            Log::info("Datos recibidos para guardar Encuesta", ['request' => $request->all(), 'idIndicador' => $idIndicador]);

            // Verificar si idIndicador es válido
            if (!$idIndicador || !is_numeric($idIndicador)) {
                Log::error(" Error: idIndicador no válido", ['idIndicador' => $idIndicador]);
                return response()->json(['message' => 'ID de indicador inválido'], 400);
            }

            // Extraer los datos de la encuesta
            $data = $request->get('result');

            // Validar que los datos de la encuesta existen
            if (!$data) {
                Log::error(" Error: Datos de encuesta no recibidos.");
                return response()->json(['message' => 'No se enviaron datos de la encuesta'], 400);
            }

            // Crear o actualizar la encuesta en la base de datos
            $encuesta = Encuesta::updateOrCreate(
                ['idIndicador' => $idIndicador],
                [
                    'malo' => isset($data['malo']) ? (int) $data['malo'] : 0,
                    'regular' => isset($data['regular']) ? (int) $data['regular'] : 0,
                    'bueno' => isset($data['bueno']) ? (int) $data['bueno'] : 0,
                    'excelente' => isset($data['excelente']) ? (int) $data['excelente'] : 0,
                    'noEncuestas' => isset($data['noEncuestas']) ? (int) $data['noEncuestas'] : 0,
                ]
            );

            Log::info("Encuesta guardada correctamente", ['idIndicador' => $idIndicador, 'datos' => $encuesta]);

            return response()->json([
                'message' => 'Encuesta guardada exitosamente',
                'encuesta' => $encuesta
            ], 201);
        } catch (\Exception $e) {
            Log::error(" Error al guardar Encuesta", ['idIndicador' => $idIndicador, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al guardar encuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los resultados de una encuesta.
     */
    public function show($idIndicador)
    {
        try {
            Log::info("Obteniendo resultados de Encuesta para idIndicador:", ['idIndicador' => $idIndicador]);

            $encuesta = Encuesta::where('idIndicador', $idIndicador)->first();

            if (!$encuesta) {
                Log::warning(" No se encontraron datos de encuesta para este indicador", ['idIndicador' => $idIndicador]);
                return response()->json([
                    'message' => 'No se encontraron resultados para este indicador',
                    'encuesta' => null
                ], 404);
            }

            return response()->json(['encuesta' => $encuesta], 200);
        } catch (\Exception $e) {
            Log::error(" Error al obtener Encuesta", ['idIndicador' => $idIndicador, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al obtener encuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
