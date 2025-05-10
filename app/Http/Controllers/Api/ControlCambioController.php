<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ControlCambio;
use Illuminate\Http\Request;

class ControlCambioController extends Controller
{
    // Obtener todos los registros
    public function index()
    {
        return response()->json(ControlCambio::all());
    }
    
    // Guardar un nuevo registro
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'idProceso' => 'required|integer',
            'idArchivo' => 'required|integer',
            'seccion' => 'required|string|max:255',
            'edicion' => 'required|integer',
            'version' => 'required|integer',
            'fechaRevision' => 'nullable|date',
            'descripcion' => 'required|string',
        ]);

        $controlCambio = ControlCambio::create($validatedData);

        return response()->json($controlCambio, 201);
    }

    // Obtener un registro por ID
    public function show($id)
    {
        $controlCambio = ControlCambio::find($id);
        if (!$controlCambio) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
        return response()->json($controlCambio);
    }

    // Actualizar un registro
    public function update(Request $request, $id)
    {
        $controlCambio = ControlCambio::find($id);
        if (!$controlCambio) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'idProceso' => 'integer',
            'idArchivo' => 'integer',
            'seccion' => 'string|max:255',
            'edicion' => 'integer',
            'version' => 'integer',
            'fechaRevision' => 'date',
            'descripcion' => 'string',
        ]);

        $controlCambio->update($validatedData);

        return response()->json($controlCambio);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $controlCambio = ControlCambio::find($id);
        if (!$controlCambio) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $controlCambio->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    public function porProceso($idProceso)
    {
        $cambios = ControlCambio::where('idProceso', $idProceso)->get();
        return response()->json($cambios);
    }
}
