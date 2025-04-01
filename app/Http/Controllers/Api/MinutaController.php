<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Asistente;
use App\Models\SeguimientoMinuta;
use App\Models\CompromisoMinuta;
use App\Models\ActividadMinuta;
use App\Http\Controllers\Controller;

class MinutaController extends Controller
{
    public function index()
    {
        try {
            Log::info("Obteniendo todas las minutas...");

            $minutas = SeguimientoMinuta::with(['actividades', 'asistentes', 'compromisos'])->get();

            Log::info("Minutas obtenidas exitosamente.");
            return response()->json($minutas);
        } catch (\Exception $e) {
            Log::error("Error al obtener minutas: " . $e->getMessage());
            return response()->json(['error' => 'Error al obtener las minutas'], 500);
        }
    }

    public function store(Request $request)
{
    try {
        Log::info('Intentando crear una nueva minuta...', ['request' => $request->all()]);

        $seguimiento = SeguimientoMinuta::create($request->only(['idRegistro', 'lugar', 'fecha', 'duracion']));
        Log::info('Minuta creada con ID: ' . $seguimiento->idSeguimiento);

        // Verificar si existen las claves antes de recorrerlas
        if (isset($request->actividades) && is_array($request->actividades)) {
            foreach ($request->actividades as $actividad) {
                Log::info('Insertando actividad:', ['actividad' => $actividad]);
                ActividadMinuta::create([
                    'idSeguimiento' => $seguimiento->idSeguimiento,
                    'descripcion' => $actividad['descripcion'],
                ]);
            }
        }

        if (isset($request->asistentes) && is_array($request->asistentes)) {
            foreach ($request->asistentes as $asistente) {
                Log::info('Insertando asistente:', ['asistente' => $asistente]);
                Asistente::create([
                    'idSeguimiento' => $seguimiento->idSeguimiento,
                    'nombre' => $asistente, 
                ]);
            }
        }

        if (isset($request->compromisos) && is_array($request->compromisos)) {
            foreach ($request->compromisos as $compromiso) {
                Log::info('Insertando compromiso:', ['compromiso' => $compromiso]);
                CompromisoMinuta::create([
                    'idSeguimiento' => $seguimiento->idSeguimiento,
                    'descripcion' => $compromiso['descripcion'], 
                    'responsables' => $compromiso['responsable'],
                    'fecha' => $compromiso['fechaCompromiso'],
                ]);
            }
        }

        return response()->json(['message' => 'Minuta creada correctamente', 'data' => $seguimiento], 201);
    } catch (Exception $e) {
        Log::error('Error al crear minuta: ' . $e->getMessage());
        return response()->json(['error' => 'Error interno del servidor'], 500);
    }
}
    public function show($id)
    {
        try {
            Log::info("Buscando minuta con ID: " . $id);

            $minuta = SeguimientoMinuta::with(['actividades', 'asistentes', 'compromisos'])->findOrFail($id);

            Log::info("Minuta encontrada: ", ['minuta' => $minuta]);
            return response()->json($minuta);
        } catch (\Exception $e) {
            Log::error("Error al obtener la minuta: " . $e->getMessage());
            return response()->json(['error' => 'Minuta no encontrada'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("Intentando actualizar minuta con ID: " . $id, ['request' => $request->all()]);

            $seguimiento = SeguimientoMinuta::findOrFail($id);
            $seguimiento->update($request->only(['idRegistro', 'lugar', 'fecha', 'duracion']));

            foreach ($request->actividades as $actividad) {
                Log::info("Actualizando actividad: ", $actividad);
                ActividadMinuta::updateOrCreate(
                    ['idActividadMin' => $actividad['idActividadMin']],
                    ['descripcion' => $actividad['descripcion']]
                );
            }

            foreach ($request->asistentes as $asistente) {
                Log::info("Actualizando asistente: ", $asistente);
                Asistente::updateOrCreate(
                    ['idAsistente' => $asistente['idAsistente']],
                    ['nombre' => $asistente['nombre']]
                );
            }

            foreach ($request->compromisos as $compromiso) {
                Log::info("Actualizando compromiso: ", $compromiso);
                CompromisoMinuta::updateOrCreate(
                    ['idCompromiso' => $compromiso['idCompromiso']],
                    [
                        'descripcion' => $compromiso['descripcion'],
                        'responsables' => $compromiso['responsables'],
                        'fecha' => $compromiso['fecha']
                    ]
                );
            }

            Log::info("Minuta actualizada correctamente.");
            return response()->json($seguimiento);
        } catch (\Exception $e) {
            Log::error("Error al actualizar la minuta: " . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar la minuta'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info("Intentando eliminar minuta con ID: " . $id);

            $seguimiento = SeguimientoMinuta::findOrFail($id);
            $seguimiento->delete();

            Log::info("Minuta eliminada exitosamente.");
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error("Error al eliminar la minuta: " . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar la minuta'], 500);
        }
    }

    public function getMinutasByRegistro($idRegistro)
{
    try {
        Log::info("Obteniendo minutas para idRegistro: " . $idRegistro);

        $minutas = SeguimientoMinuta::with(['actividades', 'asistentes', 'compromisos'])
                    ->where('idRegistro', $idRegistro)
                    ->get();

        if ($minutas->isEmpty()) {
            return response()->json(['message' => 'No hay minutas para este registro'], 404);
        }

        Log::info("Minutas obtenidas correctamente.");
        return response()->json($minutas);
    } catch (\Exception $e) {
        Log::error("Error al obtener minutas por idRegistro: " . $e->getMessage());
        return response()->json(['error' => 'Error al obtener minutas'], 500);
    }
}

}
