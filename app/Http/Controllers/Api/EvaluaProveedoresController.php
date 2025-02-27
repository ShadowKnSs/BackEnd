<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EvaluaProveedores;
use App\Models\AnalisisDatos;
use Illuminate\Support\Facades\Log;

class EvaluaProveedoresController extends Controller
{
    /*
     * Registra o actualiza la evaluación de proveedores para un indicador dado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idIndicador  (corresponde al idIndicadorConsolidado en analisisdatos)
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $idIndicador)
    {
        try {
            // Buscar en la tabla analisisdatos el registro que tenga este idIndicadorConsolidado
            $analisisRegistro = AnalisisDatos::where('idIndicadorConsolidado', $idIndicador)->first();
            if (!$analisisRegistro) {
                Log::error("No se encontró registro en analisisdatos para idIndicadorConsolidado: {$idIndicador}");
                return response()->json([
                    'message' => 'No se encontró el registro en analisisdatos',
                ], 404);
            }

            // Obtener el idIndicador real a partir del registro encontrado
            $realIdIndicador = $analisisRegistro->idIndicador;

            $data = $request->get('result');

            // Convertir los valores a enteros o null si están vacíos
            $confiable = isset($data['confiable']) && $data['confiable'] !== "" ? (int)$data['confiable'] : null;
            $condicionado = isset($data['condicionado']) && $data['condicionado'] !== "" ? (int)$data['condicionado'] : null;
            $noConfiable = isset($data['noConfiable']) && $data['noConfiable'] !== "" ? (int)$data['noConfiable'] : null;
            
            // Actualiza o crea la evaluación para este indicador utilizando el id real
            $evaluacion = EvaluaProveedores::updateOrCreate(
                ['idIndicador' => $realIdIndicador],
                [
                    'confiable'   => $confiable,
                    'condicionado'=> $condicionado,
                    'noConfiable' => $noConfiable,
                ]
            );

            Log::info("Evaluación de proveedores registrada para indicador {$idIndicador} (real id: {$realIdIndicador})", [
                'evaluacion' => $evaluacion
            ]);

            return response()->json([
                'message' => 'Evaluación de proveedores registrada exitosamente',
                'evaluacion' => $evaluacion
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al registrar evaluación de proveedores para indicador {$idIndicador}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar la evaluación de proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($idIndicadorConsolidado)
    {
        try {
            // Buscar en analisisdatos el registro que tenga el idIndicadorConsolidado
            $analisisRegistro = AnalisisDatos::where('idIndicadorConsolidado', $idIndicadorConsolidado)->first();
            
            if (!$analisisRegistro) {
                Log::error("No se encontró registro en analisisdatos para idIndicadorConsolidado: {$idIndicadorConsolidado}");
                return response()->json([
                    'message' => 'No se encontró el registro en analisisdatos.'
                ], 404);
            }
            
            // Obtener el idIndicador real a partir del registro encontrado
            $realIdIndicador = $analisisRegistro->idIndicador;
            
            // Buscar la evaluación utilizando el id real
            $evaluacion = EvaluaProveedores::where('idIndicador', $realIdIndicador)->first();
            
            if (!$evaluacion) {
                Log::info("No se encontró evaluación de proveedores para idIndicador: {$realIdIndicador}");
                return response()->json([
                    'message' => 'No se encontró la evaluación de proveedores.'
                ], 404);
            }
            
            return response()->json([
                'evaluacion' => $evaluacion
            ], 200);
            
        } catch (\Exception $e) {
            Log::error("Error al obtener la evaluación para idIndicadorConsolidado {$idIndicadorConsolidado}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la evaluación de proveedores.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

