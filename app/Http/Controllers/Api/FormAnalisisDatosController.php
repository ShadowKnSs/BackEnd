<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormAnalisisDatos;
use App\Models\AnalisisDatos;
use App\Models\Encuesta;
use App\Models\EvaluaProveedores;
use App\Models\Retroalimentacion;
use App\Models\IndicadorConsolidado;


class FormAnalisisDatosController extends Controller
{
    // Obtener registros de AnalisisDatos filtrando por idRegistro
    public function show($idRegistro)
    {
        // Buscar registros en AnalisisDatos por idRegistro
        $registros = AnalisisDatos::where('idRegistro', $idRegistro)->get();
        $indicadores = IndicadorConsolidado::where('idRegistro', $idRegistro)->get();

        // Si no se encuentran registros, devolver error
        if ($registros->isEmpty()) {
            return response()->json(['message' => 'No se encontraron registros con el idRegistro proporcionado'], 404);
        }

        // Extraer los idIndicador de los indicadores
        $idIndicadores = $indicadores->pluck('idIndicador')->toArray();

        // Obtener registros de Encuesta, EvaluaProveedores y Retroalimentacion que tengan algún idIndicador
        $encuestas = Encuesta::whereIn('idIndicador', $idIndicadores)->get();
        $evaluaProveedores = EvaluaProveedores::whereIn('idIndicador', $idIndicadores)->get();
        $retroalimentaciones = Retroalimentacion::whereIn('idIndicador', $idIndicadores)->get();

        // Devolver los resultados en formato JSON
        return response()->json([
            'AnalisisDatos' => $registros,
            'Indicadores' => $indicadores,
            'idIndicadores' => $idIndicadores, // Puedes incluirlo en la respuesta si lo necesitas
            'Encuestas' => $encuestas,
            'EvaluaProveedores' => $evaluaProveedores,
            'Retroalimentaciones' => $retroalimentaciones
        ]);
    }

    /* Actualizar necesidad e interpretación en NeceInter
    public function updateNecesidadInterpretacion(Request $request, $idformAnalisisDatos)
    {
        // Validar los datos de entrada
        $request->validate([
            'pestana' => 'required|string',
            'campo' => 'required|string|in:necesidad,interpretacion',
            'valor' => 'required|string',
        ]);

        // Buscar el registro en NeceInter por idformAnalisisDatos y pestaña
        $neceInter = NeceInter::where('idformAnalisisDatos', $idformAnalisisDatos)
            ->where('pestana', $request->pestana)
            ->first();

        // Si no se encuentra el registro, devolver error
        if (!$neceInter) {
            return response()->json(['message' => 'Registro de NeceInter no encontrado'], 404);
        }

        // Actualizar el campo correspondiente
        $campo = $request->campo;
        $neceInter->$campo = $request->valor;
        $neceInter->save();

        // Devolver respuesta exitosa
        return response()->json(['message' => 'Campo actualizado correctamente', 'data' => $neceInter], 200);
    }
        */
}