<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormAnalisisDatos;
use App\Models\AnalisisDatos;
use App\Models\Encuesta;
use App\Models\EvaluaProveedores;
use App\Models\Retroalimentacion;
use App\Models\NeceInter;

class FormAnalisisDatosController extends Controller
{
    // Obtener un registro de FormAnalisisDatos junto con sus análisis de datos
    public function show($idformAnalisisDatos)
    {
        // Buscar el registro en FormAnalisisDatos por su ID
        $formAnalisisDatos = FormAnalisisDatos::find($idformAnalisisDatos);

        // Si no se encuentra el registro, devolver error
        if (!$formAnalisisDatos) {
            return response()->json(['message' => 'Registro de Análisis de Datos no encontrado'], 404);
        }

        // Consultar los riesgos asociados al idformAnalisisDatos
        $indicadores = AnalisisDatos::where('idformAnalisisDatos', $idformAnalisisDatos)->get();
        $encuesta = Encuesta::where('idformAnalisisDatos', $idformAnalisisDatos)->get();
        $evProv = EvaluaProveedores::where('idformAnalisisDatos', $idformAnalisisDatos)->get();
        $retro = Retroalimentacion::where('idformAnalisisDatos', $idformAnalisisDatos)->get();
        $neceInter = NeceInter::where('idformAnalisisDatos', $idformAnalisisDatos)->get();
        

        // Devolver los resultados
        return response()->json([
            'formAnalisisDatos' => $formAnalisisDatos,
            'Indicadores' => $indicadores,
            'Encuesta' => $encuesta,
            'Evaluacion' => $evProv,
            'Retroalimentacion' => $retro,
            'NeceInter'=>$neceInter
        ]);
    }

    // Actualizar necesidad e interpretación en NeceInter
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
}