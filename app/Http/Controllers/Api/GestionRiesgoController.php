<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GestionRiesgos;
use App\Models\Riesgo;
use Illuminate\Support\Facades\Log; // Importar Log para depuración

class GestionRiesgoController extends Controller
{
    /**
     * Muestra los riesgos de ambas tablas basados en el idGesRies.
     */
    public function getRiesgosByGesRies($idGesRies)
    {
        // Buscar el registro en gestionriesgos por el idGesRies
        $gestionRiesgos = GestionRiesgos::where('idGesRies', $idGesRies)->first();

        // Si no se encuentra el registro en gestionriesgos, devolver error
        if (!$gestionRiesgos) {
            return response()->json(['message' => 'Gestión de riesgos no encontrada'], 404);
        }

        // Consultar los riesgos asociados al idGesRies
        $riesgos = Riesgo::where('idGesRies', $idGesRies)->get();

        // Devolver los resultados de ambas tablas
        return response()->json([
            'gestionRiesgos' => $gestionRiesgos,
            'riesgos' => $riesgos
        ]);
    }

    /**
     * Almacena un nuevo riesgo asociado a una gestión de riesgos.
     */
    public function store(Request $request, $idGesRies)
    {
        // Depuración en consola y logs
        Log::info('Entrando al método store', ['idGesRies' => $idGesRies, 'data' => $request->all()]);
        error_log('Entrando al método store con idGesRies: ' . $idGesRies . ' y datos: ' . json_encode($request->all()));

        $request->validate([
            'responsable' => 'required|string|max:255',
            'fuente' => 'nullable|string|max:255',
            'tipoRiesgo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'consecuencias' => 'nullable|string',
            'valorSeveridad' => 'required|integer|min:1|max:10',
            'valorOcurrencia' => 'required|integer|min:1|max:10',
            'valorNRP' => 'required|integer',
            'actividades' => 'nullable|string',
            'accionMejora' => 'nullable|string',
            'fechaImp' => 'nullable|date',
            'fechaEva' => 'nullable|date',
            'reevaluacionSeveridad' => 'nullable|integer|min:1|max:10',
            'reevaluacionOcurrencia' => 'nullable|integer|min:1|max:10',
            'reevaluacionNRP' => 'nullable|integer',
            'reevaluacionEfectividad' => 'nullable|integer|min:1|max:10',
            'analisisEfectividad' => 'nullable|string'
        ]);

        // Crear el riesgo asignando el idGesRies desde la URL
        $riesgo = Riesgo::create(array_merge($request->all(), ['idGesRies' => $idGesRies]));

        // Confirmación en consola y logs
        Log::info('Riesgo almacenado correctamente', ['riesgo' => $riesgo]);
        error_log('Riesgo almacenado correctamente: ' . json_encode($riesgo));

        return response()->json([
            'message' => 'Riesgo almacenado correctamente',
            'riesgo' => $riesgo
        ], 201);
    }

    /**
     * Actualiza un riesgo existente.
     */
    public function update(Request $request, $idGesRies, $idRiesgo)
    {
        // Depuración en consola y logs
        Log::info('Entrando al método update', ['idGesRies' => $idGesRies, 'idRiesgo' => $idRiesgo, 'data' => $request->all()]);
        error_log('Entrando al método update con idGesRies: ' . $idGesRies . ', idRiesgo: ' . $idRiesgo . ' y datos: ' . json_encode($request->all()));

        // Validar los datos de entrada
        $request->validate([
            'responsable' => 'sometimes|string|max:255',
            'fuente' => 'nullable|string|max:255',
            'tipoRiesgo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'consecuencias' => 'nullable|string',
            'valorSeveridad' => 'sometimes|integer|min:1|max:10',
            'valorOcurrencia' => 'sometimes|integer|min:1|max:10',
            'valorNRP' => 'sometimes|integer',
            'actividades' => 'nullable|string',
            'accionMejora' => 'nullable|string',
            'fechaImp' => 'nullable|date',
            'fechaEva' => 'nullable|date',
            'reevaluacionSeveridad' => 'nullable|integer|min:1|max:10',
            'reevaluacionOcurrencia' => 'nullable|integer|min:1|max:10',
            'reevaluacionNRP' => 'nullable|integer',
            'reevaluacionEfectividad' => 'nullable|integer|min:1|max:10',
            'analisisEfectividad' => 'nullable|string'
        ]);

        // Buscar el riesgo específico asociado al idGesRies
        $riesgo = Riesgo::where('idGesRies', $idGesRies)->find($idRiesgo);

        // Si no se encuentra el riesgo, devolver error
        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado'], 404);
        }

        // Actualizar el riesgo con los datos proporcionados
        $riesgo->update($request->all());

        // Confirmación en consola y logs
        Log::info('Riesgo actualizado correctamente', ['riesgo' => $riesgo]);
        error_log('Riesgo actualizado correctamente: ' . json_encode($riesgo));

        return response()->json([
            'message' => 'Riesgo actualizado correctamente',
            'riesgo' => $riesgo
        ]);
    }

    /**
     * Elimina un riesgo existente.
     */
    public function delete($idGesRies, $idRiesgo)
    {
        // Depuración en consola y logs
        Log::info('Entrando al método delete', ['idGesRies' => $idGesRies, 'idRiesgo' => $idRiesgo]);
        error_log('Entrando al método delete con idGesRies: ' . $idGesRies . ' y idRiesgo: ' . $idRiesgo);

        // Buscar el riesgo específico asociado al idGesRies
        $riesgo = Riesgo::where('idGesRies', $idGesRies)->find($idRiesgo);

        // Si no se encuentra el riesgo, devolver error
        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado'], 404);
        }

        // Eliminar el riesgo
        $riesgo->delete();

        // Confirmación en consola y logs
        Log::info('Riesgo eliminado correctamente', ['idRiesgo' => $idRiesgo]);
        error_log('Riesgo eliminado correctamente: ' . $idRiesgo);

        return response()->json([
            'message' => 'Riesgo eliminado correctamente'
        ], 204);
    }
}