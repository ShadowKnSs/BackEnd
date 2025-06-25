<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanTrabajo;
use App\Models\ActividadMejora;
use App\Models\FuentePt;
use App\Models\Riesgo;
use App\Models\Registros;
use App\Models\GestionRiesgos;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PlanTrabajoController extends Controller
{
    // Listado de planes de trabajo (con actividad de mejora y fuentes)
    public function index($id)
    {
        $plan = PlanTrabajo::with(['actividadMejora', 'fuentes'])->findOrFail($id);
        return response()->json($plan);
    }


    // Crear un plan de trabajo, junto con la actividad de mejora (si no se envía idActividadMejora) y las fuentes asociadas
    public function store(Request $request)
    {
        Log::info("📥 Iniciando creación de plan de trabajo", $request->all());

        $request->validate([
            'idRegistro' => 'required|integer|exists:Registros,idRegistro',
            'responsable' => 'required|string|max:255',
            'fechaElaboracion' => 'required|date',
            'objetivo' => 'required|string|max:255',
            'revisadoPor' => 'nullable|string|max:100',
            'fechaRevision' => 'nullable|date',
            'elaboradoPor' => 'nullable|string|max:255',
            'fuentes' => 'nullable|array',
            'fuentes.*.responsable' => 'required_with:fuentes|string|max:255',
            'fuentes.*.fechaInicio' => 'required_with:fuentes|date',
            'fuentes.*.fechaTermino' => 'required_with:fuentes|date|after_or_equal:fuentes.*.fechaInicio',
            'fuentes.*.estado' => 'required_with:fuentes|in:En proceso,Cerrado',
            'fuentes.*.nombreFuente' => 'required_with:fuentes|string|max:255',
            'fuentes.*.elementoEntrada' => 'required_with:fuentes|string',
            'fuentes.*.descripcion' => 'required_with:fuentes|string|max:255',
            'fuentes.*.entregable' => 'required_with:fuentes|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $actividad = ActividadMejora::where('idRegistro', $request->idRegistro)->first();
            if (!$actividad) {
                Log::warning("⚠️ ActividadMejora no encontrada para idRegistro: {$request->idRegistro}");
                return response()->json(['message' => 'No existe actividad de mejora para este registro'], 404);
            }

            $plan = PlanTrabajo::firstOrNew(['idActividadMejora' => $actividad->idActividadMejora]);
            $plan->fill($request->only([
                'responsable',
                'fechaElaboracion',
                'objetivo',
                'revisadoPor',
                'fechaRevision',
                'elaboradoPor'
            ]));
            $plan->save();

            // Asociar fuentes con upsert si hay
            if ($request->has('fuentes') && is_array($request->fuentes)) {
                $fuentes = collect($request->fuentes)->map(function ($fuente) use ($plan) {
                    return array_merge($fuente, ['idPlanTrabajo' => $plan->idPlanTrabajo]);
                })->toArray();

                FuentePt::where('idPlanTrabajo', $plan->idPlanTrabajo)->delete(); // Optional: limpiar duplicados antiguos
                FuentePt::insert($fuentes); // Mejor que múltiples `create()`
            }

            DB::commit();

            return response()->json([
                'message' => 'Plan y fuentes creados exitosamente.',
                'planTrabajo' => $plan->load('actividadMejora', 'fuentes')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error al guardar plan de trabajo: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al guardar el plan'], 500);
        }
    }



    // Mostrar un plan de trabajo específico
    public function show($id)
    {
        Log::info("Mostrando plan de trabajo con id: " . $id);
        $planTrabajo = PlanTrabajo::with('actividadMejora', 'fuentes')->find($id);
        if (!$planTrabajo) {
            Log::warning("Plan de trabajo no encontrado para id: " . $id);
            return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
        }
        return response()->json($planTrabajo, 200);
    }

    // Actualizar un plan de trabajo y opcionalmente sus fuentes
    public function update(Request $request, $id)
    {
        Log::info("🔄 Iniciando actualización del Plan de Trabajo ID={$id}");

        $planTrabajo = PlanTrabajo::find($id);
        if (!$planTrabajo) {
            Log::warning("⚠️ No se encontró el plan de trabajo con ID={$id}");
            return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
        }

        Log::info("📥 Datos recibidos para actualizar:", $request->all());

        $validator = Validator::make($request->all(), [
            'fechaElaboracion' => 'sometimes|date',
            'objetivo' => 'sometimes|string|max:255',
            'revisadoPor' => 'sometimes|string|max:100',
            'responsable' => 'sometimes|string|max:255',
            'elaboradoPor' => 'sometimes|string|max:255',
            'fechaRevision' => 'sometimes|date',

            'fuentes' => 'sometimes|array|min:1',
            'fuentes.*.noActividad' => 'required|integer|min:1',
            'fuentes.*.responsable' => 'required|string|max:255',
            'fuentes.*.fechaInicio' => 'required|date',
            'fuentes.*.fechaTermino' => 'required|date|after_or_equal:fuentes.*.fechaInicio',
            'fuentes.*.estado' => 'required|in:En proceso,Cerrado',
            'fuentes.*.nombreFuente' => 'required|string|max:255',
            'fuentes.*.elementoEntrada' => 'required|string',
            'fuentes.*.descripcion' => 'required|string|max:255',
            'fuentes.*.entregable' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error("❌ Falló la validación de los datos:", $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $planData = $request->only([
                'fechaElaboracion',
                'objetivo',
                'revisadoPor',
                'responsable',
                'elaboradoPor',
                'fechaRevision'
            ]);

            Log::info("✏️ Actualizando datos básicos del plan:", $planData);
            $planTrabajo->update($planData);
            Log::info("✅ Datos del plan actualizados correctamente");

            if ($request->has('fuentes')) {
                Log::info("🔁 Actualizando fuentes (cantidad: " . count($request->fuentes) . ")");

                // Eliminar fuentes anteriores
                $deleted = FuentePt::where('idPlanTrabajo', $planTrabajo->idPlanTrabajo)->delete();
                Log::info("🗑️ Fuentes anteriores eliminadas: {$deleted}");

                foreach ($request->fuentes as $i => $fuente) {
                    Log::info("➕ Insertando fuente [{$i}]:", $fuente);

                    $nueva = new FuentePt([
                        'idPlanTrabajo' => $planTrabajo->idPlanTrabajo,
                        'responsable' => $fuente['responsable'],
                        'fechaInicio' => $fuente['fechaInicio'],
                        'fechaTermino' => $fuente['fechaTermino'],
                        'estado' => $fuente['estado'],
                        'nombreFuente' => $fuente['nombreFuente'],
                        'elementoEntrada' => $fuente['elementoEntrada'],
                        'descripcion' => $fuente['descripcion'],
                        'entregable' => $fuente['entregable'],
                    ]);
                    $nueva->save();

                    Log::info("📌 Fuente creada con idFuente={$nueva->idFuente}");

                    // === Asociar con Riesgo ===
                    $actividad = ActividadMejora::find($planTrabajo->idActividadMejora);
                    if (!$actividad) {
                        Log::warning("⛔ No se encontró ActividadMejora id={$planTrabajo->idActividadMejora}");
                        continue;
                    }

                    $registroBase = Registros::find($actividad->idRegistro);
                    if (!$registroBase) {
                        Log::warning("⛔ Registro base no encontrado para actividad={$actividad->idActividadMejora}");
                        continue;
                    }

                    $registroGR = Registros::where('idProceso', $registroBase->idProceso)
                        ->where('año', $registroBase->año)
                        ->where('Apartado', 'Gestión de Riesgo')
                        ->first();
                    if (!$registroGR) {
                        Log::warning("⛔ Registro de gestión de riesgo no encontrado");
                        continue;
                    }

                    $gestion = GestionRiesgos::where('idRegistro', $registroGR->idRegistro)->first();
                    if (!$gestion) {
                        Log::warning("⛔ Gestión de riesgo no encontrada para registro id={$registroGR->idRegistro}");
                        continue;
                    }

                    $accionMejora = 'PT-' . str_pad($fuente['noActividad'], 2, '0', STR_PAD_LEFT);

                    $riesgo = Riesgo::where('idGesRies', $gestion->idGesRies)
                        ->where('descripcion', $fuente['elementoEntrada'])
                        ->first();

                    if ($riesgo) {
                        Log::info("🔄 Actualizando riesgo existente id={$riesgo->idRiesgo}");
                        $riesgo->update([
                            'actividades' => $fuente['descripcion'],
                            'responsable' => $fuente['responsable'],
                            'accionMejora' => $accionMejora,
                        ]);
                    } else {
                        Log::info("🆕 Creando nuevo riesgo para fuente={$nueva->idFuente}");
                        $nuevoRiesgo = Riesgo::create([
                            'idGesRies' => $gestion->idGesRies,
                            'idFuente' => $nueva->idFuente,
                            'descripcion' => $fuente['elementoEntrada'],
                            'actividades' => $fuente['descripcion'],
                            'accionMejora' => $accionMejora,
                            'responsable' => $fuente['responsable'],
                            'valorSeveridad' => 1,
                            'valorOcurrencia' => 1,
                            'valorNRP' => 1,
                        ]);
                        Log::info("✅ Riesgo creado id={$nuevoRiesgo->idRiesgo}");
                    }
                }
            }


            DB::commit();
            Log::info("✅ Plan y fuentes actualizados correctamente");

            return response()->json([
                'message' => 'Plan y fuentes actualizados correctamente',
                'planTrabajo' => $planTrabajo
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error al actualizar plan de trabajo: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al actualizar plan de trabajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un plan de trabajo (y sus fuentes, en cascada)
    public function destroy($id)
    {
        Log::info("Eliminando plan de trabajo con id: " . $id);
        $planTrabajo = PlanTrabajo::find($id);
        if (!$planTrabajo) {
            Log::warning("Plan de trabajo no encontrado para eliminar, id: " . $id);
            return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
        }
        $planTrabajo->delete();
        Log::info("Plan de trabajo eliminado exitosamente, id: " . $id);
        return response()->json(['message' => 'Plan de trabajo eliminado exitosamente'], 200);
    }

    public function getByRegistro($idRegistro)
    {
        Log::info("Obteniendo plan de trabajo por idRegistro: " . $idRegistro);
        $plan = PlanTrabajo::with('actividadMejora', 'fuentes')
            ->whereHas('actividadMejora', function ($q) use ($idRegistro) {
                $q->where('idRegistro', $idRegistro);
            })->first();

        if (!$plan) {
            Log::warning("Plan de trabajo no encontrado para idRegistro: " . $idRegistro);
            return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
        }
        return response()->json($plan, 200);
    }

}
