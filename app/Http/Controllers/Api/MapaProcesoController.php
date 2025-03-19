<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MapaProceso;

class MapaProcesoController extends Controller {
    // Obtener todos los registros
    public function index() {
        return response()->json(MapaProceso::all());
    }

    // Obtener un solo registro
    public function show($id) {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }
        return response()->json($mapaProceso);
    }

    // Crear un nuevo registro
    public function store(Request $request) {
        $request->validate([
            'idProceso' => 'required|exists:procesos,idProceso',
            'documentos' => 'nullable|string',
            'fuente' => 'nullable|string',
            'material' => 'nullable|string',
            'requisitos' => 'nullable|string',
            'salidas' => 'nullable|string',
            'receptores' => 'nullable|string',
            'puestosInvolucrados' => 'nullable|string'
        ]);

        $mapaProceso = MapaProceso::create($request->all());
        return response()->json($mapaProceso, 201);
    }

    // Actualizar un registro
    public function update(Request $request, $id) {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        $mapaProceso->update($request->all());
        return response()->json($mapaProceso);
    }

    // Eliminar un registro
    public function destroy($id) {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        $mapaProceso->delete();
        return response()->json(['message' => 'Mapa de proceso eliminado correctamente']);
    }
}
