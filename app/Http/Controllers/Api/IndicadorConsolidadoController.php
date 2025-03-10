<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Retroalimentacion;
use App\Models\Encuesta;
use App\Models\EvaluaProveedores;
use App\Models\AnalisisDatos;
use App\Models\IndicadorConsolidado;
use Illuminate\Support\Facades\Log;

class IndicadorConsolidadoController extends Controller
{
    public function index()
    {
        $indicadores = IndicadorConsolidado::all();
        return response()->json(['indicadores' => $indicadores], 200);
    }

    public function show($id)
    {
        $indicador = IndicadorConsolidado::findOrFail($id);
        return response()->json(['indicador' => $indicador], 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Crear el registro en indicadoresconsolidados
            $indicador = IndicadorConsolidado::create($request->all());

            // Crear el registro en analisisdatos (sin resultados aún)
            $analisis = AnalisisDatos::create([
                'idIndicadorConsolidado' => $indicador->idIndicadorConsolidado,
                'resultadoSemestral1' => null,
                'resultadoSemestral2' => null,
                'interpretacion' => null,
                'necesidad' => null,
                'meta' => null,
            ]);

            // Según el origen, se crea el registro en la tabla de resultados específica
            $origen = $indicador->origenIndicador;
            if ($origen === 'Encuesta') {
                DB::table('encuesta')->insert([
                    // Aquí usamos $analisis->idIndicador, que es la clave primaria autoincremental de analisisdatos
                    'idIndicador' => $analisis->idIndicador,
                    'malo' => 0,
                    'regular' => 0,
                    'excelenteBueno' => 0,
                    'noEncuestas' => 0,
                ]);
            } elseif ($origen === 'Retroalimentacion') {
                DB::table('retroalimentacion')->insert([
                    'idIndicador' => $analisis->idIndicador,
                    'metodo' => $request->get('metodo') ?? '',
                    'cantidadFelicitacion' => 0,
                    'cantidadSugerencia' => 0,
                    'cantidadQueja' => 0,
                ]);
            } elseif ($origen === 'EvaluaProveedores') {
                DB::table('evaluaProveedores')->insert([
                    'idIndicador' => $analisis->idIndicador,
                    'confiable' => 0,
                    'condicionado' => 0,
                    'noConfiable' => 0,
                ]);
            }
            
            DB::commit();
            Log::info('Indicador creado exitosamente', [
                'id' => $indicador->idIndicadorConsolidado,
                'nombre' => $indicador->nombreIndicador,
            ]);
            return response()->json([
                'message' => 'Indicador creado exitosamente',
                'indicador' => $indicador
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear indicador', [
                'error' => $e->getMessage(),
                'datos' => $request->all()
            ]);
            return response()->json([
                'message' => 'Error al crear el indicador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // 1) Actualizas la parte de indicadoresconsolidados
        \DB::beginTransaction();
        try {
            // 1) Actualizar la tabla indicadoresconsolidados
            $indicador = IndicadorConsolidado::findOrFail($id);
    
            // Solo los campos que quieres actualizar
            $indicador->update($request->only([
                'nombreIndicador',
        'descripcionIndicador',
        'origenIndicador',
        'periodicidad'
            ]));
    
            // 2) Obtenemos el registro de analisisdatos
            $analisis = AnalisisDatos::where('idIndicadorConsolidado', $id)->first();
            if (!$analisis) {
                // Si no existe, podría ser un error
                throw new \Exception("No se encontró 'analisisdatos' para este indicadorConsolidado $id");
            }
    
            $realId = $analisis->idIndicador;  // Este es el ID real en la tabla analisisdatos
    
            // 3) Dependiendo del origenIndicador, actualizar la tabla correspondiente
            switch ($indicador->origenIndicador) {
                case 'Retroalimentacion':
                    // Tomamos 'metodo' si viene, sino 'N/A'
                    $metodo = $request->get('metodo') ?? 'N/A';
                    Retroalimentacion::updateOrCreate(
                        ['idIndicador' => $realId],
                        [
                            'metodo' => $metodo,
                            // Si deseas ponerlos en 0 cuando se edita:
                            'cantidadFelicitacion' => 0,
                            'cantidadSugerencia' => 0,
                            'cantidadQueja' => 0,
                        ]
                    );
                    break;
    
                case 'Encuesta':
                    // Podrías hacer algo similar
                    Encuesta::updateOrCreate(
                        ['idIndicador' => $realId],
                        [
                            'malo' => 0,
                            'regular' => 0,
                            'excelenteBueno' => 0,
                            'noEncuestas' => 0,
                        ]
                    );
                    break;
    
                case 'EvaluaProveedores':
                    EvaluaProveedores::updateOrCreate(
                        ['idIndicador' => $realId],
                        [
                            'confiable' => 0,
                            'condicionado' => 0,
                            'noConfiable' => 0,
                        ]
                    );
                    break;
    
                default:
                    // Si no es ninguno de esos, no hacemos nada especial
                    break;
            }
    
            \DB::commit();
            return response()->json(['indicador' => $indicador], 200);
    
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Error al actualizar indicador $id: ".$e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el indicador',
                'error' => $e->getMessage()
            ], 500);
        }

}
}
