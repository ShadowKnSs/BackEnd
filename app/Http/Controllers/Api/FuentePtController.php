<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FuentePt;
use Illuminate\Support\Facades\Validator;

class FuentePtController extends Controller
{
    public function index()
    {
        $fuentes = FuentePt::all();
        return response()->json($fuentes, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idPlanTrabajo' => 'required|integer',
            'responsable' => 'required|string|max:255',
            'fechaInicio' => 'required|date',
            'fechaTermino' => 'required|date',
            'estado' => 'required|in:En proceso,Cerrado',
            'nombreFuente' => 'required|string|max:255',
            'elementoEntrada' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'entregable' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $fuente = FuentePt::create($request->all());
        return response()->json([
            'message' => 'Fuente creada exitosamente',
            'fuente' => $fuente
        ], 201);
    }

    public function show($id)
    {
        $fuente = FuentePt::find($id);
        if (!$fuente) {
            return response()->json(['message' => 'Fuente no encontrada'], 404);
        }
        return response()->json($fuente, 200);
    }

    public function update(Request $request, $id)
    {
        $fuente = FuentePt::find($id);
        if (!$fuente) {
            return response()->json(['message' => 'Fuente no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'responsable' => 'sometimes|required|string|max:255',
            'fechaInicio' => 'sometimes|required|date',
            'fechaTermino' => 'sometimes|required|date',
            'estado' => 'sometimes|required|in:En proceso,Cerrado',
            'nombreFuente' => 'sometimes|required|string|max:255',
            'elementoEntrada' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string|max:255',
            'entregable' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $fuente->update($request->all());
        return response()->json([
            'message' => 'Fuente actualizada exitosamente',
            'fuente' => $fuente
        ], 200);
    }

    public function destroy($id)
    {
        $fuente = FuentePt::find($id);
        if (!$fuente) {
            return response()->json(['message' => 'Fuente no encontrada'], 404);
        }
        $fuente->delete();
        return response()->json(['message' => 'Fuente eliminada exitosamente'], 200);
    }
}
