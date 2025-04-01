<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MapaProceso;

class MapaProcesoController extends Controller
{
    // Obtener todos los registros
    public function index($idProceso)
    {
        $mapa = MapaProceso::where('idProceso', $idProceso)->first();

        if ($mapa) {
            return response()->json($mapa);
        } else {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }
    }

    // Obtener un solo registro
    public function show($idProceso)
{
    $mapaProceso = MapaProceso::where('idProceso', $idProceso)->first();

    if (!$mapaProceso) {
        \Log::warning("⚠️ MapaProceso no encontrado con idProceso: $idProceso");
        return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
    }

    \Log::info("✅ MapaProceso encontrado con idProceso: $idProceso");

    return response()->json($mapaProceso);
}

    // Crear un nuevo registro
    public function store(Request $request)
    {
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
    public function update(Request $request, $id)
    {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        $mapaProceso->update($request->all());
        return response()->json($mapaProceso);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        $mapaProceso->delete();
        return response()->json(['message' => 'Mapa de proceso eliminado correctamente']);
    }

    //Funcion para subir y gaurdar la imagen del diagrama de flujo
    public function subirDiagramaFlujo(Request $request, $idProceso)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        try {
            $mapa = MapaProceso::where('idProceso', $idProceso)->firstOrFail();
    
            $file = $request->file('imagen');
    
            $filename = 'diagrama_flujo_proceso_' . $idProceso . '.' . $file->getClientOriginalExtension();
    
            // ✅ Usamos disk "public"
            Storage::disk('public')->putFileAs('diagramas', $file, $filename);
    
            // ✅ Generamos la URL completa para que React y Blade puedan usarla directamente
            $publicPath = asset('storage/diagramas/' . $filename);
    
            $mapa->diagramaFlujo = $publicPath;
            $mapa->save();
    
            return response()->json([
                'message' => 'Imagen subida exitosamente',
                'url' => $publicPath
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al subir la imagen'], 500);
        }
    }
    

}
