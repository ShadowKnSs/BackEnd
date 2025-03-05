<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;
use Illuminate\Support\Facades\Log;

class EncuestaController extends Controller
{
    public function store(Request $request, $idIndicadorConsolidado)
    {
        try {
            // Buscar el registro en analisisdatos usando el idIndicadorConsolidado
            $analisisRegistro = AnalisisDatos::where('idIndicadorConsolidado', $idIndicadorConsolidado)->first();
            if (!$analisisRegistro) {
                Log::error("No se encontró registro en analisisdatos para idIndicadorConsolidado: {$idIndicadorConsolidado}");
                return response()->json([
                    'message' => 'No se encontró el registro en analisisdatos'
                ], 404);
            }
            
            // Obtener el idIndicador real a partir del registro encontrado
            $realIdIndicador = $analisisRegistro->idIndicador;
            
            // Obtener los datos enviados en el request
            $data = $request->get('result');
            
            // Convertir los valores a enteros (o asignar 0 en caso de que no estén definidos o sean cadenas vacías)
            $malo = isset($data['malo']) && $data['malo'] !== "" ? (int)$data['malo'] : 0;
            $regular = isset($data['regular']) && $data['regular'] !== "" ? (int)$data['regular'] : 0;
            $excelenteBueno = isset($data['excelenteBueno']) && $data['excelenteBueno'] !== "" ? (int)$data['excelenteBueno'] : 0;
            $noEncuestas = isset($data['noEncuestas']) && $data['noEncuestas'] !== "" ? (int)$data['noEncuestas'] : 0;
            
            // Crear o actualizar la encuesta utilizando el idIndicador real obtenido
            $encuesta = Encuesta::updateOrCreate(
                ['idIndicador' => $realIdIndicador],
                [
                    'malo' => $malo,
                    'regular' => $regular,
                    'excelenteBueno' => $excelenteBueno,
                    'noEncuestas' => $noEncuestas,
                ]
            );
            
            Log::info("Encuesta registrada para indicador consolidado {$idIndicadorConsolidado} (real id: {$realIdIndicador})", ['encuesta' => $encuesta]);
            
            return response()->json([
                'message' => 'Encuesta registrada exitosamente',
                'encuesta' => $encuesta
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al registrar encuesta para indicador consolidado {$idIndicadorConsolidado}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar la encuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($idIndicador)
    {
        try {
            // Buscar la encuesta asociada al idIndicador
            $encuesta = Encuesta::where('idIndicador', $idIndicador)->first();

            if (!$encuesta) {
                Log::info("No se encontró encuesta para idIndicador: {$idIndicador}");
                return response()->json([
                    'message' => 'No se encontró la encuesta.'
                ], 404);
            }

            return response()->json([
                'encuesta' => $encuesta
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al obtener la encuesta para idIndicador {$idIndicador}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la encuesta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
