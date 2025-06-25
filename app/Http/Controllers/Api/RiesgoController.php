<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Riesgo;
use App\Models\GestionRiesgos;
use App\Models\IndicadorConsolidado;
use App\Models\FuentePt;
use App\Models\ActividadMejora;
use App\Models\PlanTrabajo;

use Log;

class RiesgoController extends Controller
{
    /**
     * Muestra todos los riesgos asociados a una gestión de riesgos específica.
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
     * Crea un nuevo riesgo asociado a la gestión de riesgos (idGesRies),
     * y crea un indicador en la tabla IndicadorConsolidado.
     * POST: /api/gestionriesgos/{idGesRies}/riesgos
     */
    public function store(Request $request, $idGesRies)
    {
        DB::beginTransaction();
        try {
            // Validación
            $data = $request->validate([
                'tipoRiesgo' => 'required|string',
                'descripcion' => 'required|string',
                'valorSeveridad' => 'required|integer|min:1|max:100',
                'valorOcurrencia' => 'required|integer|min:1|max:100',
                'valorNRP' => 'required|integer',
                'fuente' => 'nullable|string',
                'consecuencias' => 'nullable|string',
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

            // Verificar existencia
            $gestion = GestionRiesgos::findOrFail($idGesRies);
            $registroGestion = Registros::findOrFail($gestion->idregistro);

            $idProceso = $registroGestion->idProceso;
            $año = $registroGestion->año;

            // Obtener registro de indicadores
            $registroIndicadores = Registros::where([
                ['idProceso', $idProceso],
                ['año', $año],
                ['Apartado', 'Gestión de Riesgo']
            ])->firstOrFail();

            // Crear riesgo
            $data['idGesRies'] = $idGesRies;
            $riesgo = Riesgo::create($data);

            // Crear indicador
            $indicador = IndicadorConsolidado::create([
                'idRegistro' => $registroIndicadores->idRegistro,
                'idProceso' => $idProceso,
                'nombreIndicador' => $riesgo->descripcion,
                'origenIndicador' => 'GestionRiesgo',
                'periodicidad' => 'Anual',
                'meta' => null,
            ]);

            // Asociar fuente PT si aplica
            $actividad = ActividadMejora::whereHas('registro', function ($q) use ($idProceso, $año) {
                $q->where('idProceso', $idProceso)->where('año', $año);
            })->first();

            if ($actividad) {
                $plan = PlanTrabajo::where('idActividadMejora', $actividad->idActividadMejora)->first();
                if ($plan) {
                    $fuente = FuentePt::create([
                        'idPlanTrabajo' => $plan->idPlanTrabajo,
                        'nombreFuente' => 'GESTIÓN DE RIESGOS',
                        'elementoEntrada' => $riesgo->descripcion,
                    ]);
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
            return response()->json([
                'message' => 'Error al crear el riesgo.',
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
        $gestion = GestionRiesgos::find($idGesRies);
        if (!$gestion) {
            return response()->json(['message' => 'No existe gestionriesgos con idGesRies=' . $idGesRies], 404);
        }

        $riesgo = Riesgo::where([
            ['idGesRies', '=', $idGesRies],
            ['idRiesgo', '=', $idRiesgo]
        ])->first();

        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
        }

        return response()->json($riesgo, 200);
    }


    /**
     * Actualiza un riesgo existente.
     * PUT: /api/gestionriesgos/{idGesRies}/riesgos/{idRiesgo}
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
            'reevaluacionEfectividad' => 'nullable|string',
            'analisisEfectividad' => 'nullable|string',
        ]);

        DB::transaction(function () use ($gestion, $riesgo, $data) {
            if (isset($data['descripcion']) && $riesgo->idIndicador) {
                $indicador = IndicadorConsolidado::find($riesgo->idIndicador);
                if ($indicador) {
                    $indicador->nombreIndicador = $data['descripcion'];
                    $indicador->save();
                }
            }

            if (($data['fuente'] ?? $riesgo->fuente) === 'GESTIÓN DE RIESGOS') {
                if ($riesgo->idFuente) {
                    FuentePt::where('idFuente', $riesgo->idFuente)->update([
                        'nombreFuente' => 'GESTIÓN DE RIESGOS',
                        'elementoEntrada' => $data['descripcion'] ?? $riesgo->descripcion,
                    ]);
                } else {
                    $registro = Registros::find($gestion->idregistro);
                    $actividad = ActividadMejora::where('idRegistro', $registro->idRegistro)->first();
                    if ($actividad) {
                        $idPlanTrabajo = PlanTrabajo::where('idActividadMejora', $actividad->idActividadMejora)->value('idPlanTrabajo');
                        if ($idPlanTrabajo) {
                            $fuente = FuentePt::create([
                                'idPlanTrabajo' => $idPlanTrabajo,
                                'nombreFuente' => 'GESTIÓN DE RIESGOS',
                                'elementoEntrada' => $data['descripcion'] ?? $riesgo->descripcion,
                                'responsable' => $data['responsable'] ?? 'Por definir',
                                'fechaInicio' => now(),
                                'fechaTermino' => now()->addDays(7),
                                'estado' => 'En proceso',
                                'descripcion' => '',
                                'entregable' => '',
                            ]);
                            $data['idFuente'] = $fuente->idFuente;
                        }
                    }
                }
            }

            $riesgo->update($data);
        });

        return response()->json($riesgo, 200);
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

            $riesgo = Riesgo::where('idGesRies', $idGesRies)
                ->where('idRiesgo', $idRiesgo)
                ->first();

            if (!$riesgo) {
                Log::warning("[RiesgoController@destroy] Riesgo no encontrado con idRiesgo=$idRiesgo para gestion=$idGesRies");
                return response()->json(['message' => 'Riesgo no encontrado para esta gestión'], 404);
            }

            // Eliminar indicador consolidado si existe
            if ($riesgo->idIndicador) {
                IndicadorConsolidado::where('idIndicador', $riesgo->idIndicador)->delete();
                Log::info("[RiesgoController@destroy] IndicadorConsolidado eliminado con idIndicador={$riesgo->idIndicador}");
            }

            // Eliminar fuentePT si existe
            if ($riesgo->idFuente) {
                FuentePt::where('idFuente', $riesgo->idFuente)->delete();
                Log::info("[RiesgoController@destroy] FuentePt eliminada con idFuente={$riesgo->idFuente}");
            }

            $riesgo->delete();
            Log::info("[RiesgoController@destroy] Riesgo eliminado con idRiesgo=$idRiesgo");

            DB::commit();
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
