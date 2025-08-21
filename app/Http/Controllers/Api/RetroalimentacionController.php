<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Retroalimentacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RetroalimentacionController extends Controller
{
    /**
     * Almacena los resultados de Retroalimentación en la base de datos.
     */
    public function store(Request $request, $idIndicador)
    {
        try {
            Log::info("📌 Datos recibidos para guardar Retroalimentación", [
                'idIndicador' => $idIndicador,
                'request' => $request->all()
            ]);

            // Validamos los datos recibidos
            $data = $request->get('result');

            // Guardamos en la base de datos
            $retroalimentacion = Retroalimentacion::updateOrCreate(
                ['idIndicador' => $idIndicador],
                [
                    'cantidadFelicitacion' => isset($data['cantidadFelicitacion']) ? (int) $data['cantidadFelicitacion'] : 0,
                    'cantidadSugerencia' => isset($data['cantidadSugerencia']) ? (int) $data['cantidadSugerencia'] : 0,
                    'cantidadQueja' => isset($data['cantidadQueja']) ? (int) $data['cantidadQueja'] : 0
                ]
            );

            Log::info("✅ Retroalimentación guardada correctamente", [
                'idIndicador' => $idIndicador,
                'datos' => $retroalimentacion
            ]);

            return response()->json([
                'message' => 'Retroalimentación registrada exitosamente',
                'resultado' => $retroalimentacion
            ], 200);
        } catch (\Exception $e) {
            Log::error("❌ Error al guardar Retroalimentación", [
                'idIndicador' => $idIndicador,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Error al registrar la retroalimentación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera los resultados de Retroalimentación de un indicador específico.
     */
    public function show($idIndicador)
    {
        try {
            Log::info("📌 Buscando resultados de Retroalimentación", [
                'idIndicador' => $idIndicador
            ]);

            $resultado = Retroalimentacion::where('idIndicador', $idIndicador)->first();

            if (!$resultado) {
                Log::warning("⚠️ No se encontraron resultados para el indicador", [
                    'idIndicador' => $idIndicador
                ]);
                return response()->json([
                    'message' => 'No se encontraron resultados para este indicador',
                    'resultado' => null
                ], 404);
            }

            Log::info("✅ Resultados obtenidos", [
                'idIndicador' => $idIndicador,
                'resultado' => $resultado
            ]);

            return response()->json(['resultado' => $resultado], 200);
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener los resultados de Retroalimentación", [
                'idIndicador' => $idIndicador,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los resultados de Retroalimentación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function batch(Request $request)
    {
       $ids = $request->input('ids', []);
    if (!is_array($ids) || empty($ids)) {
        return response()->json([], 200);
    }

    $resultados = DB::table('retroalimentacion as r')
        ->join('IndicadoresConsolidados as i', 'r.idIndicador', '=', 'i.idIndicador')
        ->whereIn('r.idIndicador', $ids)
        ->select([
            'r.idIndicador',
            'i.nombreIndicador',
            'r.cantidadFelicitacion',
            'r.cantidadSugerencia',
            'r.cantidadQueja'
        ])
        ->get();

    return response()->json($resultados, 200);}
}
