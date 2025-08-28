<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Registros;
use App\Models\Riesgo;
use App\Models\GestionRiesgos;
use App\Models\IndicadorConsolidado;
use App\Models\FuentePt;
use App\Models\ActividadMejora;
use App\Models\PlanTrabajo;

class RiesgoController extends Controller
{
    /**
     * GET: /api/gestionriesgos/{idGesRies}/riesgos
     */
    public function index($idGesRies)
    {
        $gestion = GestionRiesgos::find($idGesRies);
        if (!$gestion) {
            return response()->json(['message' => "No existe gestionriesgos con id $idGesRies"], 404);
        }

        $riesgos = Riesgo::where('idGesRies', $idGesRies)->get();

        return response()->json([
            'gestion' => $gestion,
            'riesgos' => $riesgos
        ]);
    }

    /**
     * POST: /api/gestionriesgos/{idGesRies}/riesgos
     * Crea el riesgo, crea el indicador (sin FK en riesgos), y opcionalmente crea una fuente PT.
     */
    public function store(Request $request, $idGesRies)
    {
        DB::beginTransaction();
        try {
            // 1) Validar datos del riesgo con mensajes más específicos
            $validator = Validator::make($request->all(), [
                'tipoRiesgo' => 'required|string|max:100',
                'descripcion' => 'required|string|max:500',
                'valorSeveridad' => 'required|integer|min:1|max:100',
                'valorOcurrencia' => 'required|integer|min:1|max:100',
                'fuente' => 'nullable|string|max:100',
                'consecuencias' => 'nullable|string|max:255',
            ], [
                'tipoRiesgo.required' => 'El tipo de riesgo es obligatorio',
                'descripcion.required' => 'La descripción del riesgo es obligatoria',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres',
                'valorSeveridad.required' => 'La severidad es requerida',
                'valorOcurrencia.required' => 'La ocurrencia es requerida',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // 2) Cargar gestión y su registro base
            $gestion = GestionRiesgos::findOrFail($idGesRies);
            $registroGestion = Registros::findOrFail($gestion->idRegistro);

            $idProceso = $registroGestion->idProceso;
            $anio = $registroGestion->año;

            // 3) Localizar el registro del apartado "Gestión de Riesgo" (con y sin acento)
            $registroIndicadores = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->whereIn('Apartado', ['Gestión de Riesgo', 'Gestion de Riesgo'])
                ->firstOrFail();


            if (empty($data['fuente'])) {
                $data['fuente'] = 'Gestión de Riesgos';
            }

            // Si vienen vacíos desde el front, usa mínimos válidos (cumple validación de 1..100)
            $data['valorSeveridad'] = max(1, (int) ($data['valorSeveridad'] ?? 1));
            $data['valorOcurrencia'] = max(1, (int) ($data['valorOcurrencia'] ?? 1));
            $data['valorNRP'] = $data['valorSeveridad'] * $data['valorOcurrencia'];
            // 4) Crear Riesgo
            $data['idGesRies'] = $idGesRies;
            $riesgo = Riesgo::create($data);

            // 5) Crear Indicador asociado (pero SIN FK en riesgos)
            $indicador = IndicadorConsolidado::create([
                'idRegistro' => $registroIndicadores->idRegistro,
                'idProceso' => $idProceso,
                'nombreIndicador' => $riesgo->descripcion,
                'origenIndicador' => 'GestionRiesgo',
                'periodicidad' => 'Anual',
                'meta' => null,
            ]);

            // 6) Si ya existe un Plan de Trabajo en el mismo proceso/año, crear una fuente mínima
            $actividad = ActividadMejora::whereHas('registro', function ($q) use ($idProceso, $anio) {
                $q->where('idProceso', $idProceso)->where('año', $anio);
            })->first();

            if ($actividad) {
                $plan = PlanTrabajo::where('idActividadMejora', $actividad->idActividadMejora)->first();
                if ($plan) {
                    // Consecutivo para noActividad (y/o numero)
                    $next = (int) (FuentePt::where('idPlanTrabajo', $plan->idPlanTrabajo)->max('noActividad') ?? 0) + 1;

                    // Defaults seguros
                    $fechaInicio = $riesgo->fechaImp ?: now()->toDateString();
                    $fechaTermino = $riesgo->fechaEva ?: $fechaInicio;
                    $estado = 'En proceso';
                    $responsable = $riesgo->responsable ?: 'Por definir';

                    // Payload base
                    $payload = [
                        'idPlanTrabajo' => $plan->idPlanTrabajo,
                        'noActividad' => $next,             // <- nombre correcto de la columna
                        'responsable' => $responsable,
                        'fechaInicio' => $fechaInicio,
                        'fechaTermino' => $fechaTermino,
                        'estado' => $estado,
                        'nombreFuente' => 'Gestión de Riesgos',
                        'elementoEntrada' => $riesgo->descripcion,
                        'descripcion' => $riesgo->actividades ?? '',
                        'entregable' => '',
                    ];

                    $fuente = FuentePt::create($payload);

                    // opcional: guardar idFuente en riesgo si quieres relación hacia PT
                    $riesgo->update(['idFuente' => $fuente->idFuente]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Riesgo e indicador creados correctamente.',
                'riesgo' => $riesgo,
                'indicador' => $indicador
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en RiesgoController@store: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error al crear el riesgo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * PUT: /api/gestionriesgos/{idGesRies}/riesgos/{idRiesgo}
     * Actualiza el riesgo y, si cambia la descripción, sincroniza el nombre del indicador.
     */
    public function update(Request $request, $idGesRies, $idRiesgo)
    {
        $gestion = GestionRiesgos::find($idGesRies);
        if (!$gestion) {
            return response()->json(['message' => 'No existe gestionriesgos con idGesRies=' . $idGesRies], 404);
        }

        $riesgo = Riesgo::where('idGesRies', $idGesRies)->where('idRiesgo', $idRiesgo)->first();
        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
        }

        $data = $request->validate([
            'fuente' => 'nullable|string',
            'tipoRiesgo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'consecuencias' => 'nullable|string',
            'valorSeveridad' => 'nullable|integer|min:1|max:100',
            'valorOcurrencia' => 'nullable|integer|min:1|max:100',
            'valorNRP' => 'nullable|integer',
            'actividades' => 'nullable|string',
            'accionMejora' => 'nullable|string',
            'fechaImp' => 'nullable|date',
            'fechaEva' => 'nullable|date',
            'responsable' => 'nullable|string',
            'reevaluacionSeveridad' => 'nullable|integer|min:1|max:100',
            'reevaluacionOcurrencia' => 'nullable|integer|min:1|max:100',
            'reevaluacionNRP' => 'nullable|integer',
            'reevaluacionEfectividad' => 'nullable|string',
            'analisisEfectividad' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Guardamos la descripción previa para localizar el indicador
            $oldDescripcion = $riesgo->descripcion;

            // 1) Actualizar riesgo
            $riesgo->update($data);

            // 2) Sincronizar el nombre del indicador si cambió la descripción
            if (array_key_exists('descripcion', $data) && $data['descripcion'] !== $oldDescripcion) {
                $registroGestion = Registros::findOrFail($gestion->idRegistro);

                $indicador = IndicadorConsolidado::where('idRegistro', $registroGestion->idRegistro)
                    ->where('origenIndicador', 'GestionRiesgo')
                    ->where('nombreIndicador', $oldDescripcion)
                    ->first();

                if ($indicador) {
                    $indicador->update(['nombreIndicador' => $riesgo->descripcion]);
                }
            }

            // 3) Si la fuente PT está marcada como Gestión de Riesgos, actualizar/crear fuente mínima
            if (($data['fuente'] ?? $riesgo->fuente) === 'Gestión de Riesgos') {
                // Si el riesgo ya tiene fuente, la sincronizamos
                if ($riesgo->idFuente) {
                    FuentePt::where('idFuente', $riesgo->idFuente)->update([
                        'nombreFuente' => 'Gestión de Riesgos',
                        'elementoEntrada' => $riesgo->descripcion,
                        'responsable' => $riesgo->responsable,
                        'descripcion' => $riesgo->actividades,
                    ]);
                } else {
                    // crear si existe plan
                    $registroBase = Registros::find($gestion->idRegistro);
                    $actividad = ActividadMejora::where('idRegistro', $registroBase->idRegistro)->first();
                    if ($actividad) {
                        $plan = PlanTrabajo::where('idActividadMejora', $actividad->idActividadMejora)->first();
                        if ($plan) {
                            $fuente = FuentePt::create([
                                'idPlanTrabajo' => $plan->idPlanTrabajo,
                                'nombreFuente' => 'Gestión de Riesgos',
                                'elementoEntrada' => $riesgo->descripcion,
                                'responsable' => $riesgo->responsable,
                                'fechaInicio' => $riesgo->fechaImp ?? null,
                                'fechaTermino' => $riesgo->fechaEva ?? null,
                                'estado' => null,
                                'descripcion' => $riesgo->actividades ?? null,
                                'entregable' => null,
                            ]);
                            $riesgo->update(['idFuente' => $fuente->idFuente]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json($riesgo, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en RiesgoController@update: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error al actualizar el riesgo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE: /api/gestionriesgos/{idGesRies}/riesgos/{idRiesgo}
     * Elimina el riesgo, su indicador (buscado por nombre/origen/registro) y su fuente PT si existe.
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

            $riesgo = Riesgo::where('idGesRies', $idGesRies)
                ->where('idRiesgo', $idRiesgo)
                ->first();

            if (!$riesgo) {
                Log::warning("[RiesgoController@destroy] Riesgo no encontrado con idRiesgo=$idRiesgo para gestion=$idGesRies");
                return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
            }

            // 1) Eliminar indicador asociado (sin FK): buscar por idRegistro + origen + nombre
            $registroGestion = Registros::findOrFail($gestion->idRegistro);
            IndicadorConsolidado::where('idRegistro', $registroGestion->idRegistro)
                ->where('origenIndicador', 'GestionRiesgo')
                ->where('nombreIndicador', $riesgo->descripcion)
                ->delete();

            // 2) Eliminar fuente PT si hay
            if ($riesgo->idFuente) {
                FuentePt::where('idFuente', $riesgo->idFuente)->delete();
                Log::info("[RiesgoController@destroy] FuentePt eliminada idFuente={$riesgo->idFuente}");
            }

            // 3) Eliminar riesgo
            $riesgo->delete();
            Log::info("[RiesgoController@destroy] Riesgo eliminado idRiesgo=$idRiesgo");

            DB::commit();
            return response()->json(['message' => 'Riesgo eliminado correctamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[RiesgoController@destroy] Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error al eliminar el Riesgo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function evaluarEfectividad(Request $request, $idGesRies, $idRiesgo)
    {
        $validator = Validator::make($request->all(), [
            'reevaluacionSeveridad' => 'required|integer|min:1|max:100',
            'reevaluacionOcurrencia' => 'required|integer|min:1|max:100',
            'analisisEfectividad' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $riesgo = Riesgo::where('idGesRies', $idGesRies)
            ->where('idRiesgo', $idRiesgo)
            ->firstOrFail();

        $data = $validator->validated();
        $data['reevaluacionNRP'] = $data['reevaluacionSeveridad'] * $data['reevaluacionOcurrencia'];

        // Determinar efectividad
        $valorNRP = $riesgo->valorSeveridad * $riesgo->valorOcurrencia;
        $data['reevaluacionEfectividad'] = $data['reevaluacionNRP'] < $valorNRP ? 'Mejoró' : 'No mejoró';

        $riesgo->update($data);

        return response()->json([
            'message' => 'Evaluación de efectividad guardada correctamente',
            'riesgo' => $riesgo
        ]);
    }
}
