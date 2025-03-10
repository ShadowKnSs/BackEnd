<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Retroalimentacion;
use App\Models\AnalisisDatos;
use Illuminate\Support\Facades\Log;

class RetroalimentacionController extends Controller
{
    /**
     * Registra o actualiza la retroalimentación para un indicador dado.
     *
     * Se espera que el request contenga un objeto "result" con:
     * { 
     *   metodo: <valor>,
     *   cantidadFelicitacion: <valor>,
     *   cantidadSugerencia: <valor>,
     *   cantidadQueja: <valor>
     * }
     *
     * La lógica es:
     * - Se recibe el idIndicadorConsolidado.
     * - Se busca en la tabla analisisdatos el registro correspondiente para obtener el idIndicador real.
     * - Finalmente, se usa ese id para crear o actualizar la retroalimentación.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $idIndicadorConsolidado
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $idIndicadorConsolidado)
    {
        try {
            // Buscar en analisisdatos el registro asociado al idIndicadorConsolidado
            $analisisRegistro = AnalisisDatos::where('idIndicadorConsolidado', $idIndicadorConsolidado)->first();
            if (!$analisisRegistro) {
                Log::error("No se encontró registro en analisisdatos para idIndicadorConsolidado: {$idIndicadorConsolidado}");
                return response()->json([
                    'message' => 'No se encontró el registro en analisisdatos'
                ], 404);
            }
            
            // Verificar que el registro tenga un idIndicador válido
            if (!$analisisRegistro->idIndicador) {
                Log::error("El registro en analisisdatos para idIndicadorConsolidado {$idIndicadorConsolidado} tiene idIndicador nulo");
                return response()->json([
                    'message' => 'El registro en analisisdatos no tiene idIndicador asignado'
                ], 500);
            }
            
            // Obtener el idIndicador real
            $realIdIndicador = $analisisRegistro->idIndicador;
            
            $data = $request->get('result');
            
            
            // Convertir los demás valores a enteros o asignar 0 si no se especifican
            $cantidadFelicitacion = isset($data['cantidadFelicitacion']) && $data['cantidadFelicitacion'] !== "" 
                ? (int)$data['cantidadFelicitacion'] 
                : 0;
            $cantidadSugerencia = isset($data['cantidadSugerencia']) && $data['cantidadSugerencia'] !== ""
                ? (int)$data['cantidadSugerencia']
                : 0;
            $cantidadQueja = isset($data['cantidadQueja']) && $data['cantidadQueja'] !== ""
                ? (int)$data['cantidadQueja']
                : 0;
            

                $existingRetro = Retroalimentacion::where('idIndicador', $realIdIndicador)->first();

            // Si no se recibe un valor para 'metodo' o viene vacío, asignamos un valor por defecto, por ejemplo, "N/A"
            $metodo = isset($data['metodo']) && trim($data['metodo']) !== ""
            ? $data['metodo']
            
            : ($existingRetro ? $existingRetro->metodo : "N/A");
            // Crear o actualizar la retroalimentación usando el idIndicador real
            $retro = Retroalimentacion::updateOrCreate(
                ['idIndicador' => $realIdIndicador],
                [
                    'metodo' => $metodo,
                    'cantidadFelicitacion' => $cantidadFelicitacion,
                    'cantidadSugerencia' => $cantidadSugerencia,
                    'cantidadQueja' => $cantidadQueja,
                ]
            );
            
            Log::info("Retroalimentación registrada para indicador consolidado {$idIndicadorConsolidado} (real id: {$realIdIndicador})", ['retro' => $retro]);
            
            return response()->json([
                'message' => 'Retroalimentación registrada',
                'retro' => $retro
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al registrar retroalimentación para indicador consolidado {$idIndicadorConsolidado}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar la retroalimentación',
                'error' => $e->getMessage()
            ], 500);
        }
    }


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
            $retroalimentacion = Retroalimentacion::where('idIndicador', $realIdIndicador)->first();
            if (!$retroalimentacion) {
                Log::info("No se encontró encuesta para idIndicador: {$realIdIndicador}");
                return response()->json([
                    'message' => 'No se encontró la retroalimentación.'
                ], 404);
            }

            return response()->json([
                'retroalimentacion' => $retroalimentacion
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
