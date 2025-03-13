<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActividadMejora;
use Illuminate\Support\Facades\Validator;

class ActividadMejoraController extends Controller
{
    public function index()
    {
        $actividades = ActividadMejora::all();
        return response()->json($actividades, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idRegistro' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $actividad = ActividadMejora::create($request->only('idRegistro'));
        return response()->json([
            'message' => 'Actividad de mejora creada exitosamente',
            'actividadMejora' => $actividad
        ], 201);
    }

    public function show($id)
    {
        $actividad = ActividadMejora::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'Actividad de mejora no encontrada'], 404);
        }
        return response()->json($actividad, 200);
    }

    public function update(Request $request, $id)
    {
        $actividad = ActividadMejora::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'Actividad de mejora no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'idRegistro' => 'sometimes|required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $actividad->update($request->only('idRegistro'));
        return response()->json([
            'message' => 'Actividad de mejora actualizada exitosamente',
            'actividadMejora' => $actividad
        ], 200);
    }

    public function destroy($id)
    {
        $actividad = ActividadMejora::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'Actividad de mejora no encontrada'], 404);
        }
        $actividad->delete();
        return response()->json(['message' => 'Actividad de mejora eliminada exitosamente'], 200);
    }
}
