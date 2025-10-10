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
            \Log::warning("MapaProceso no encontrado con idProceso: $idProceso");
            return response()->json(['message' => 'Mapa de proceso no encontrado'], 404);
        }

        \Log::info("MapaProceso encontrado con idProceso: $idProceso");

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
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB máximo
        ]);

        try {
            $mapa = MapaProceso::where('idProceso', $idProceso)->firstOrFail();

            $file = $request->file('imagen');
            $filename = 'diagrama_flujo_proceso_' . $idProceso . '_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diagramas', $file, $filename);

            $publicPath = asset('storage/diagramas/' . $filename);

            // Si ya existe un diagrama anterior, eliminarlo
            if ($mapa->diagramaFlujo) {
                $this->eliminarArchivoDiagrama($mapa->diagramaFlujo);
            }

            $mapa->diagramaFlujo = $publicPath;
            $mapa->save();

            ControlCambiosService::registrarCambio(
                $idProceso,
                'Diagrama de Flujo',
                'subió',
                'Se subió un nuevo diagrama de flujo: ' . $file->getClientOriginalName()
            );

            return response()->json([
                'message' => 'Imagen subida exitosamente',
                'url' => $publicPath
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al subir diagrama de flujo: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina el diagrama de flujo de un proceso
     */
    public function eliminarDiagramaFlujo($idProceso)
    {
        try {
            $mapa = MapaProceso::where('idProceso', $idProceso)->first();

            if (!$mapa) {
                return response()->json([
                    'message' => 'No se encontró el mapa de proceso'
                ], 404);
            }

            if (!$mapa->diagramaFlujo) {
                return response()->json([
                    'message' => 'No hay diagrama de flujo para eliminar'
                ], 404);
            }

            // Guardar información para el registro de cambios
            $nombreArchivo = basename($mapa->diagramaFlujo);

            // Eliminar el archivo físico
            $this->eliminarArchivoDiagrama($mapa->diagramaFlujo);

            // Limpiar el campo en la base de datos
            $mapa->diagramaFlujo = null;
            $mapa->save();

            // Registrar el cambio
            ControlCambiosService::registrarCambio(
                $idProceso,
                'Diagrama de Flujo',
                'eliminó',
                'Se eliminó el diagrama de flujo: ' . $nombreArchivo
            );

            return response()->json([
                'message' => 'Diagrama de flujo eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al eliminar diagrama de flujo: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al eliminar el diagrama de flujo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Función auxiliar para eliminar el archivo físico del diagrama
     */
    private function eliminarArchivoDiagrama($diagramaPath)
    {
        try {
            // Extraer el nombre del archivo de la URL completa
            $filename = basename($diagramaPath);
            $filePath = 'diagramas/' . $filename;

            // Verificar si el archivo existe y eliminarlo
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                \Log::info("Archivo eliminado: " . $filePath);
            } else {
                \Log::warning("Archivo no encontrado para eliminar: " . $filePath);
            }
        } catch (\Exception $e) {
            \Log::error('Error al eliminar archivo físico del diagrama: ' . $e->getMessage());
            // No lanzamos excepción aquí para no interrumpir el flujo principal
        }
    }
}