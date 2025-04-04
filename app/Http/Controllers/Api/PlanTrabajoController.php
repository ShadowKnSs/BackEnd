<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanTrabajo;
use App\Models\ActividadMejora;
use App\Models\FuentePt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PlanTrabajoController extends Controller
{
    // Listado de planes de trabajo (con actividad de mejora y fuentes)
    public function index()
    {
        Log::info("Obteniendo listado de planes de trabajo.");
        $planTrabajos = PlanTrabajo::with('actividadMejora', 'fuentes')->get();
        return response()->json($planTrabajos, 200);
    }

    // Crear un plan de trabajo, junto con la actividad de mejora (si no se envía idActividadMejora) y las fuentes asociadas
    public function store(Request $request)
    {
        Log::info("Iniciando creación de plan de trabajo.", $request->all());

        $validator = Validator::make($request->all(), [
            'planTrabajo.fechaElaboracion' => 'required|date',
            'planTrabajo.objetivo' => 'required|string|max:255',
            'planTrabajo.revisadoPor' => 'required|string|max:100',
            'planTrabajo.responsable' => 'required|string|max:100',
            // Validamos el objeto actividadMejora, esperando que contenga idRegistro
            'planTrabajo.actividadMejora.idRegistro' => 'required|integer',
            'fuentes' => 'required|array|min:1',
            'fuentes.*.responsable' => 'required|string|max:255',
            'fuentes.*.fechaInicio' => 'required|date',
            'fuentes.*.fechaTermino' => 'required|date',
            'fuentes.*.estado' => 'required|in:En proceso,Cerrado',
            'fuentes.*.nombreFuente' => 'required|string|max:255',
            'fuentes.*.elementoEntrada' => 'required|string|max:255',
            'fuentes.*.descripcion' => 'required|string|max:255',
            'fuentes.*.entregable' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            Log::error("Error de validación en store:", $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }

        $planData = $request->input('planTrabajo');
        Log::info("Datos del plan recibidos", $planData);

        // Si el objeto actividadMejora no contiene un idRegistro válido, creamos una nueva actividad
        if (!isset($planData['actividadMejora']['idRegistro']) || empty($planData['actividadMejora']['idRegistro'])) {
            Log::info("No se envió idRegistro en actividadMejora, se creará una nueva actividad de mejora.");
            // En este caso, decidimos qué valor utilizar; sin embargo, dado que el flujo indica que se debe recibir el idRegistro desde la vista Carpetas,
            // este error no debería ocurrir. Si ocurre, puedes lanzar un error o asignar un valor por defecto.
            return response()->json(['message' => 'No se recibió el idRegistro en actividadMejora'], 422);
        } else {
            // Si se envía idRegistro, creamos la actividad de mejora y asignamos su id a planData.
            $actividadData = $planData['actividadMejora']; // debe tener idRegistro
            Log::info("Creando actividad de mejora con idRegistro:", $actividadData);
            $actividad = ActividadMejora::create(['idRegistro' => $actividadData['idRegistro']]);
            Log::info("Actividad de mejora creada", ['idActividadMejora' => $actividad->idActividadMejora]);
            $planData['idActividadMejora'] = $actividad->idActividadMejora;
        }

        try {
            $planTrabajo = PlanTrabajo::create($planData);
            Log::info("Plan de trabajo creado", ['idPlanTrabajo' => $planTrabajo->idPlanTrabajo]);

            $fuentesData = $request->input('fuentes');
            foreach ($fuentesData as $fuente) {
                $fuente['idPlanTrabajo'] = $planTrabajo->idPlanTrabajo;
                $nuevaFuente = FuentePt::create($fuente);
                Log::info("Fuente creada", ['idFuente' => $nuevaFuente->idFuente]);
            }

            Log::info("Plan de trabajo creado exitosamente, enviando respuesta.");
            return response()->json([
                'message' => 'Plan de trabajo creado exitosamente',
                'planTrabajo' => $planTrabajo->load('actividadMejora', 'fuentes')
            ], 201);
        } catch (\Exception $e) {
            Log::error("Excepción al crear plan de trabajo: " . $e->getMessage());
            return response()->json(['message' => 'Error al crear el plan de trabajo'], 500);
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
        Log::info("Actualizando plan de trabajo con id: " . $id);
        $planTrabajo = PlanTrabajo::find($id);
        if (!$planTrabajo) {
            Log::warning("Plan de trabajo no encontrado para actualizar, id: " . $id);
            return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'planTrabajo.fechaElaboracion' => 'sometimes|required|date',
            'planTrabajo.objetivo' => 'sometimes|required|string|max:255',
            'planTrabajo.revisadoPor' => 'sometimes|required|string|max:100',
            'planTrabajo.responsable' => 'sometimes|required|string|max:100',
        ]);

        if ($validator->fails()) {
            Log::error("Error de validación en update:", $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }

        $planData = $request->input('planTrabajo', []);
        $planTrabajo->update($planData);
        Log::info("Plan de trabajo actualizado", ['idPlanTrabajo' => $planTrabajo->idPlanTrabajo]);

        if ($request->has('fuentes')) {
            Log::info("Actualizando fuentes para el plan de trabajo id: " . $planTrabajo->idPlanTrabajo);
            FuentePt::where('idPlanTrabajo', $planTrabajo->idPlanTrabajo)->delete();
            foreach ($request->input('fuentes') as $fuente) {
                $fuente['idPlanTrabajo'] = $planTrabajo->idPlanTrabajo;
                FuentePt::create($fuente);
            }
        }

        return response()->json([
            'message' => 'Plan de trabajo actualizado exitosamente',
            'planTrabajo' => $planTrabajo->load('actividadMejora', 'fuentes')
        ], 200);
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
        ->whereHas('actividadMejora', function($q) use ($idRegistro) {
            $q->where('idRegistro', $idRegistro);
        })->first();

    if (!$plan) {
        Log::warning("Plan de trabajo no encontrado para idRegistro: " . $idRegistro);
        return response()->json(['message' => 'Plan de trabajo no encontrado'], 404);
    }
    return response()->json($plan, 200);
}

}
