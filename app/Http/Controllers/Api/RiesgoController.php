<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Riesgo;
use App\Models\GestionRiesgos;
use App\Models\IndicadorConsolidado;
use Log;

class RiesgoController extends Controller
{
    /**
     * Muestra todos los riesgos asociados a una gestión de riesgos específica.
     * GET: /api/gestionriesgos/{idGesRies}/riesgos
     */
    public function index($idGesRies)
    {
        // LOG: Inicio del método index
        Log::info("[RiesgoController@index] Ingresando con idGesRies=$idGesRies");

        // 1) Verificar que exista la fila en gestionriesgos
        $gestion = GestionRiesgos::find($idGesRies);

        if (!$gestion) {
            Log::warning("[RiesgoController@index] No existe gestionriesgos con idGesRies=$idGesRies");
            return response()->json(['message' => 'No existe gestionriesgos con idGesRies=' . $idGesRies], 404);
        }

        // 2) Obtener todos los riesgos asociados
        $riesgos = Riesgo::where('idGesRies', $idGesRies)->get();
        // LOG: Cantidad de riesgos encontrados
        Log::info("[RiesgoController@index] Se encontraron " . count($riesgos) . " riesgos para la gestión $idGesRies");

        return response()->json([
            'gestionRiesgos' => $gestion,
            'riesgos' => $riesgos
        ], 200);
    }

    /**
     * Crea un nuevo riesgo asociado a la gestión de riesgos (idGesRies),
     * y crea un indicador en la tabla IndicadorConsolidado.
     * POST: /api/gestionriesgos/{idGesRies}/riesgos
     */
    public function store(Request $request, $idGesRies)
    {
        Log::info("[RiesgoController@store] Ingresando con idGesRies=$idGesRies", ['request' => $request->all()]);

        DB::beginTransaction();
        try {

            // 2) Validar campos que vengan en $request
            $data = $request->validate([
                'fuente' => 'nullable|string',
                'tipoRiesgo' => 'required|string',
                'descripcion' => 'required|string',
                'consecuencias' => 'nullable|string',
                'valorSeveridad' => 'required|integer|min:1|max:100',
                'valorOcurrencia' => 'required|integer|min:1|max:100',
                'valorNRP' => 'required|integer',
                'actividades' => 'nullable|string',
                'accionMejora' => 'nullable|string',
                'fechaImp' => 'nullable|date',
                'fechaEva' => 'nullable|date',
                'responsable' => 'required|string',
                'reevaluacionSeveridad' => 'nullable|integer|min:1|max:100',
                'reevaluacionOcurrencia' => 'nullable|integer|min:1|max:100',
                'reevaluacionNRP' => 'nullable|integer',
                'reevaluacionEfectividad' => 'nullable|integer|min:1|max:100',
                'analisisEfectividad' => 'nullable|string',
            ]);

            // Asignar el idGesRies proveniente de la URL
            $data['idGesRies'] = $idGesRies;
            // LOG: Campos validados
            Log::info("[RiesgoController@store] Campos validados para Riesgo:", $data);

            // 3) Crear el registro en la tabla 'riesgos'

            $riesgo = Riesgo::create($data);

            // LOG: Riesgo creado
            Log::info("[RiesgoController@store] Riesgo creado con idRiesgo=" . $riesgo->idRiesgo);

            $gestion = GestionRiesgos::find($idGesRies);
            if (!$gestion) {
                return response()->json(['message' => 'Gestión de riesgos no encontrada.'], 404);
            }

            $registro = Registros::find($gestion->idregistro);
            if (!$registro) {
                return response()->json(['message' => 'Gestión de riesgos no encontrada.'], 404);
            }

            // 5) Crear el indicador en 'IndicadorConsolidado'
            $indicador = IndicadorConsolidado::create([
                'idRegistro' => $gestion->idregistro,
                'idProceso' => $registro->idProceso,
                'nombreIndicador' => $riesgo->descripcion,
                'origenIndicador' => 'GestionRiesgo',
                'periodicidad' => 'Anual',
                'meta' => null,
            ]);
            // LOG: Indicador creado
            Log::info("[RiesgoController@store] Indicador creado con id=" . $indicador->idIndicadorConsolidado);

            DB::commit();
            Log::info("[RiesgoController@store] Riesgo e indicador creados exitosamente.");
            return response()->json([
                'message' => 'Riesgo e indicador creados exitosamente.',
                'riesgo' => $riesgo,
                'indicador' => $indicador,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[RiesgoController@store] Error al crear Riesgo e indicador: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al crear el Riesgo y el indicador.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un riesgo específico asociado a una gestión.
     * GET: /api/gestionriesgos/{idGesRies}/riesgos/{idRiesgo}
     */
    public function show($idGesRies, $idRiesgo)
    {
        Log::info("[RiesgoController@show] Buscando gestionriesgos con idGesRies=$idGesRies, idRiesgo=$idRiesgo");

        // Verificar que exista la gestion
        $gestion = GestionRiesgos::find($idGesRies);
        if (!$gestion) {
            Log::warning("[RiesgoController@show] No existe gestionriesgos con idGesRies=$idGesRies");
            return response()->json(['message' => 'No existe gestionriesgos con idGesRies=' . $idGesRies], 404);
        }

        // Buscar el riesgo
        $riesgo = Riesgo::where('idGesRies', $idGesRies)->where('idRiesgo', $idRiesgo)->first();
        if (!$riesgo) {
            Log::warning("[RiesgoController@show] Riesgo no encontrado para esta gestión idRiesgo=$idRiesgo");
            return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
        }

        Log::info("[RiesgoController@show] Riesgo hallado con idRiesgo=$idRiesgo");
        return response()->json($riesgo, 200);
    }

    /**
     * Actualiza un riesgo existente.
     * PUT: /api/gestionriesgos/{idGesRies}/riesgos/{idRiesgo}
     */
    public function update(Request $request, $idGesRies, $idRiesgo)
    {
        Log::info("[RiesgoController@update] Actualizando riesgo idRiesgo=$idRiesgo para gestion=$idGesRies", [
            'payload' => $request->all()
        ]);

        DB::beginTransaction();
        try {
            $gestion = GestionRiesgos::find($idGesRies);
            if (!$gestion) {
                Log::warning("[RiesgoController@update] No existe gestionriesgos con idGesRies=$idGesRies");
                return response()->json(['message' => 'No existe gestionriesgos con idGesRies=' . $idGesRies], 404);
            }

            // Encontrar el riesgo asociado a esa gestion
            $riesgo = Riesgo::where('idGesRies', $idGesRies)->where('idRiesgo', $idRiesgo)->first();
            if (!$riesgo) {
                Log::warning("[RiesgoController@update] Riesgo no encontrado con idRiesgo=$idRiesgo para gestion=$idGesRies");
                return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
            }

            // Validar campos
            $data = $request->validate([
                'fuente' => 'nullable|string',
                'tipoRiesgo' => 'nullable|string',
                'descripcion' => 'nullable|string',
                'consecuencias' => 'nullable|string',
                'valorSeveridad' => 'nullable|integer|min:1|max:10',
                'valorOcurrencia' => 'nullable|integer|min:1|max:10',
                'valorNRP' => 'nullable|integer',
                'actividades' => 'nullable|string',
                'accionMejora' => 'nullable|string',
                'fechaImp' => 'nullable|date',
                'fechaEva' => 'nullable|date',
                'responsable' => 'nullable|string',
                'reevaluacionSeveridad' => 'nullable|integer|min:1|max:10',
                'reevaluacionOcurrencia' => 'nullable|integer|min:1|max:10',
                'reevaluacionNRP' => 'nullable|integer',
                'reevaluacionEfectividad' => 'nullable|integer|min:1|max:10',
                'analisisEfectividad' => 'nullable|string',
            ]);

            Log::info("[RiesgoController@update] Datos validados para update:", $data);

            // Actualizar el riesgo
            $riesgo->update($data);

            DB::commit();
            Log::info("[RiesgoController@update] Riesgo actualizado exitosamente, idRiesgo=$idRiesgo");
            return response()->json($riesgo, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[RiesgoController@update] Error al actualizar Riesgo: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al actualizar el Riesgo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un riesgo específico asociado a una gestión.
     * DELETE: /api/gestionriesgos/{idGesRies}/riesgos/{idRiesgo}
     */
    public function destroy($idGesRies, $idRiesgo)
    {
        Log::info("[RiesgoController@destroy] Eliminando riesgo idRiesgo=$idRiesgo de gestion=$idGesRies");

        DB::beginTransaction();
        try {
            $gestion = GestionRiesgos::find($idGesRies);
            if (!$gestion) {
                Log::warning("[RiesgoController@destroy] No existe gestionriesgos con idGesRies=$idGesRies");
                return response()->json(['message' => 'No existe gestionriesgos con idGesRies=' . $idGesRies], 404);
            }

            $riesgo = Riesgo::where('idGesRies', $idGesRies)->where('idRiesgo', $idRiesgo)->first();
            if (!$riesgo) {
                Log::warning("[RiesgoController@destroy] Riesgo no encontrado con idRiesgo=$idRiesgo para gestion=$idGesRies");
                return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
            }

            $riesgo->delete();
            DB::commit();
            Log::info("[RiesgoController@destroy] Riesgo eliminado correctamente, idRiesgo=$idRiesgo");
            return response()->json(['message' => 'Riesgo eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[RiesgoController@destroy] Error al eliminar Riesgo: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al eliminar el Riesgo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
