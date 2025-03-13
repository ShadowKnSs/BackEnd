<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ActividadControl;

class ActividadControlController extends Controller
{
    // Obtener todas las actividades
    public function index()
    {
        return response()->json(ActividadControl::all(), 200);
    }

    // Crear una nueva actividad
    public function store(Request $request)
    {
        $request->validate([
            'idProceso' => 'required|integer',
            'idFormulario' => 'required|integer',
            'idResponsable' => 'required|integer',
            'nombreActividad' => 'required|string|max:255',
            'procedimiento' => 'required|string|max:255',
            'caracteristicasVerificar' => 'required|string',
            'criterioAceptacion' => 'required|string',
            'frecuencia' => 'required|string|max:255',
            'identificacionSalida' => 'required|string',
            'registroSalida' => 'required|string',
            'tratameinto' => 'required|string'
        ]);

        $actividad = ActividadControl::create($request->all());
        return response()->json($actividad, 201);
    }

    // Mostrar una actividad específica
    public function show($id)
    {
        $actividad = ActividadControl::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        return response()->json($actividad, 200);
    }

    // Actualizar una actividad
    public function update(Request $request, $id)
    {
        $actividad = ActividadControl::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $request->validate([
            'idProceso' => 'integer',
            'idFormulario' => 'integer',
            'idResponsable' => 'integer',
            'nombreActividad' => 'string|max:255',
            'procedimiento' => 'string|max:255',
            'caracteristicasVerificar' => 'string',
            'criterioAceptacion' => 'string',
            'frecuencia' => 'string|max:255',
            'identificacionSalida' => 'string',
            'registroSalida' => 'string',
            'tratameinto' => 'string'
        ]);

        $actividad->update($request->all());
        return response()->json($actividad, 200);
    }

    // Eliminar una actividad
    public function destroy($id)
    {
        $actividad = ActividadControl::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        $actividad->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
