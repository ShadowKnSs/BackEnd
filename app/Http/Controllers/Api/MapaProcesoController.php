<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MapaProceso;
use App\Services\ControlCambiosService;

class MapaProcesoController extends Controller
{
    public function index($idProceso)
    {
        $mapa = MapaProceso::where('idProceso', $idProceso)->first();

        if ($mapa) {
            return response()->json($mapa);
        } else {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }
    }

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

    public function store(Request $request)
    {
        $request->validate([
            'idProceso' => 'required|exists:proceso,idProceso',
            'documentos' => 'nullable|string',
            'fuente' => 'nullable|string',
            'material' => 'nullable|string',
            'requisitos' => 'nullable|string',
            'salidas' => 'nullable|string',
            'receptores' => 'nullable|string',
            'puestosInvolucrados' => 'nullable|string'
        ]);

        $mapaProceso = MapaProceso::create($request->all());

        ControlCambiosService::registrarCambio(
            $request->idProceso,
            'Mapa de Proceso',
            'agregó',
            'Documentos: ' . substr($request->documentos ?? 'N/A', 0, 60)
        );

        return response()->json($mapaProceso, 201);
    }

    public function update(Request $request, $id)
    {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        $mapaProceso->update($request->all());

        ControlCambiosService::registrarCambio(
            $mapaProceso->idProceso,
            'Mapa de Proceso',
            'editó',
            'Documentos: ' . substr($mapaProceso->documentos ?? 'N/A', 0, 60)
        );

        return response()->json($mapaProceso);
    }

    public function destroy($id)
    {
        $mapaProceso = MapaProceso::find($id);
        if (!$mapaProceso) {
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        $idProceso = $mapaProceso->idProceso;
        $mapaProceso->delete();

        ControlCambiosService::registrarCambio(
            $idProceso,
            'Mapa de Proceso',
            'eliminó',
            'Se eliminó el registro completo del Mapa de Proceso'
        );

        return response()->json(['message' => 'Mapa de proceso eliminado correctamente']);
    }

    public function subirDiagramaFlujo(Request $request, $idProceso)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $mapa = MapaProceso::where('idProceso', $idProceso)->firstOrFail();

            $file = $request->file('imagen');
            $filename = 'diagrama_flujo_proceso_' . $idProceso . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diagramas', $file, $filename);

            $publicPath = asset('storage/diagramas/' . $filename);

            $mapa->diagramaFlujo = $publicPath;
            $mapa->save();

            ControlCambiosService::registrarCambio(
                $idProceso,
                'Mapa de Proceso',
                'editó',
                'Se actualizó el diagrama de flujo'
            );

            return response()->json([
                'message' => 'Imagen subida exitosamente',
                'url' => $publicPath
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al subir la imagen'], 500);
        }
    }
}
