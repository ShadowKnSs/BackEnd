<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanTrabajo;
use App\Models\ActividadMejora;
use App\Models\FuentePt;
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
        Log::info("🔄 Actualizando plan de trabajo con id: {$id}");

        $planTrabajo = PlanTrabajo::find($id);
        if (!$planTrabajo) {
            Log::warning("⚠️ Plan de trabajo no encontrado: {$id}");
            return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'planTrabajo.fechaElaboracion' => 'sometimes|date',
            'planTrabajo.objetivo' => 'sometimes|string|max:255',
            'planTrabajo.revisadoPor' => 'sometimes|string|max:100',
            'planTrabajo.responsable' => 'sometimes|string|max:255',
            'planTrabajo.elaboradoPor' => 'sometimes|string|max:255',
            'planTrabajo.fechaRevision' => 'sometimes|date',

            'fuentes' => 'sometimes|array|min:1',
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
            Log::error("❌ Errores de validación en update", $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $planData = $request->input('planTrabajo', []);
            $planTrabajo->update($planData);
            Log::info("✅ Plan actualizado: {$planTrabajo->idPlanTrabajo}");

            if ($request->has('fuentes') && is_array($request->fuentes)) {
                Log::info("🔁 Reemplazando fuentes para el plan id: {$planTrabajo->idPlanTrabajo}");

                // Borrar solo si se van a insertar nuevas
                FuentePt::where('idPlanTrabajo', $planTrabajo->idPlanTrabajo)->delete();

                $nuevas = collect($request->fuentes)->map(function ($f) use ($planTrabajo) {
                    return array_merge($f, ['idPlanTrabajo' => $planTrabajo->idPlanTrabajo]);
                })->toArray();

                FuentePt::insert($nuevas);
            }

            DB::commit();

            return response()->json([
                'message' => 'Plan de trabajo actualizado exitosamente',
                'planTrabajo' => $planTrabajo->load('actividadMejora', 'fuentes')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("💥 Error en update: {$e->getMessage()}");
            return response()->json(['message' => 'Error interno al actualizar el plan'], 500);
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
