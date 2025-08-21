<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EvaluaProveedores;
use Illuminate\Support\Facades\Log;

class EvaluaProveedoresController extends Controller
{
    public function store(Request $request, $idIndicador)
    {
        try {
            Log::info("📌 Datos recibidos para guardar Evaluación de Proveedores", [
                'idIndicador' => $idIndicador,
                'request' => $request->all()
            ]);

            // Obtener los valores enviados
            $data = $request->get('result');

            if (!$data) {
                return response()->json([
                    'message' => 'No se recibieron datos válidos'
                ], 400);
            }

            // **Verificación previa de los datos**
            Log::info("✅ Procesando datos para guardar", [
                'confiableSem1' => $data['confiableSem1'] ?? 'No recibido',
                'confiableSem2' => $data['confiableSem2'] ?? 'No recibido',
                'condicionadoSem1' => $data['condicionadoSem1'] ?? 'No recibido',
                'condicionadoSem2' => $data['condicionadoSem2'] ?? 'No recibido',
                'noConfiableSem1' => $data['noConfiableSem1'] ?? 'No recibido',
                'noConfiableSem2' => $data['noConfiableSem2'] ?? 'No recibido',
            ]);

            // Crear o actualizar el registro en evaluaProveedores
            $evaluacion = EvaluaProveedores::updateOrCreate(
                ['idIndicador' => $idIndicador],
                [
                    'resultadoConfiableSem1' => isset($data['confiableSem1']) ? (int) $data['confiableSem1'] : 0,
                    'resultadoConfiableSem2' => isset($data['confiableSem2']) ? (int) $data['confiableSem2'] : 0,
                    'resultadoCondicionadoSem1' => isset($data['condicionadoSem1']) ? (int) $data['condicionadoSem1'] : 0,
                    'resultadoCondicionadoSem2' => isset($data['condicionadoSem2']) ? (int) $data['condicionadoSem2'] : 0,
                    'resultadoNoConfiableSem1' => isset($data['noConfiableSem1']) ? (int) $data['noConfiableSem1'] : 0,
                    'resultadoNoConfiableSem2' => isset($data['noConfiableSem2']) ? (int) $data['noConfiableSem2'] : 0,
                ]
            );

            Log::info("✅ Evaluación de proveedores guardada correctamente", [
                'idIndicador' => $idIndicador,
                'datos' => $evaluacion->toArray()  // **Verificar los valores guardados**
            ]);

            return response()->json([
                'message' => 'Evaluación de proveedores guardada exitosamente',
                'resultado' => $evaluacion
            ], 200);
        } catch (\Exception $e) {
            Log::error("❌ Error al guardar Evaluación de Proveedores", [
                'idIndicador' => $idIndicador,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Error al guardar la Evaluación de Proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($idIndicador)
    {
        try {
            Log::info("📌 Buscando resultados de Evaluación de Proveedores", [
                'idIndicador' => $idIndicador
            ]);

            $evaluacion = EvaluaProveedores::where('idIndicador', $idIndicador)->first();

            if (!$evaluacion) {
                Log::warning("❌ No se encontraron resultados de Evaluación de Proveedores", [
                    'idIndicador' => $idIndicador
                ]);
                return response()->json([
                    'message' => 'No se encontraron resultados para este indicador',
                    'resultado' => null
                ], 404);
            }

            Log::info("✅ Resultados obtenidos", [
                'idIndicador' => $idIndicador,
                'resultado' => $evaluacion->toArray()  // **Confirmar valores correctos**
            ]);

            return response()->json(['resultado' => $evaluacion], 200);
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener los resultados de Evaluación de Proveedores", [
                'idIndicador' => $idIndicador,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Error al obtener los resultados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
