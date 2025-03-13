<?php

namespace App\Http\Controllers\Api;

use App\Models\IndMapaProceso;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndMapaProcesoController extends Controller
{
    // Obtener todos los registros
    public function index()
    {
        return response()->json(IndMapaProceso::all(), 200);
    }

    // Crear un nuevo registro
    public function store(Request $request)
    {
        $data = $request->validate([
            'idMapaProceso' => 'required|integer',
            'idResponsable' => 'required|integer',
            'idIndicador' => 'required|integer',
            'descripcion' => 'nullable|string',
            'formula' => 'nullable|string',
            'periodoMed' => 'nullable|string|max:50',
        ]);

        $registro = IndMapaProceso::create($data);

        return response()->json($registro, 201);
    }

    // Obtener un registro por ID
    public function show($id)
    {
        $registro = IndMapaProceso::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($registro, 200);
    }

    // Actualizar un registro
    public function update(Request $request, $id)
    {
        $registro = IndMapaProceso::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $data = $request->validate([
            'idMapaProceso' => 'integer',
            'idResponsable' => 'integer',
            'idIndicador' => 'integer',
            'descripcion' => 'nullable|string',
            'formula' => 'nullable|string',
            'periodoMed' => 'nullable|string|max:50',
        ]);

        $registro->update($data);

        return response()->json($registro, 200);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $registro = IndMapaProceso::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $registro->delete();

        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}
