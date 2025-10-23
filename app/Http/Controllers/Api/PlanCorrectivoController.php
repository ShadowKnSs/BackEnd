<?php

namespace App\Http\Controllers\ApI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanCorrectivo;
use App\Models\ActividadMejora;
use App\Models\ActividadPlan;

class PlanCorrectivoController extends Controller
{
    public function index()
    {
        $planes = PlanCorrectivo::with('actividades')->get();
        return response()->json($planes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|date',
            'origenConformidad' => 'required|string',
            'equipoMejora' => 'required|string',
            'requisito' => 'required|string',
            'incumplimiento' => 'required|string',
            'evidencia' => 'required|string',
            'coordinadorPlan' => 'required|string',
            'actividades' => 'sometimes|array',
            'actividades.*.descripcionAct' => 'required|string',
            'actividades.*.responsable' => 'required|string|max:255',
            'actividades.*.fechaProgramada' => 'required|date',
            'actividades.*.tipo' => 'required|in:reaccion,planaccion'
        ]);

        //  Buscar idActividadMejora relacionado al idRegistro
        $actividad = ActividadMejora::where('idRegistro', $request->idRegistro)->first();

        if (!$actividad) {
            return response()->json(['error' => 'No se encontró ActividadMejora asociada'], 422);
        }

        //  Encontrar el primer número disponible
        $year = date('y');
        $maxSequence = 99; // Máximo 99 planes por año

        // Obtener todos los números usados este año
        $usedNumbers = PlanCorrectivo::where('codigo', 'like', "PAC-%-$year")
            ->pluck('codigo')
            ->map(function ($code) {
                preg_match('/PAC-(\d{2})-\d{2}/', $code, $matches);
                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->filter()
            ->toArray();

        // Encontrar el primer número disponible
        $nextNumber = 1;
        for ($i = 1; $i <= $maxSequence; $i++) {
            if (!in_array($i, $usedNumbers)) {
                $nextNumber = $i;
                break;
            }
        }

        // Si todos los números están usados, usar el siguiente al máximo
        if ($nextNumber > $maxSequence) {
            $nextNumber = count($usedNumbers) > 0 ? max($usedNumbers) + 1 : 1;
        }

        $codigo = "PAC-" . str_pad($nextNumber, 2, '0', STR_PAD_LEFT) . "-" . $year;

        // Agregar manualmente el campo
        $data = $request->all();
        $data['idActividadMejora'] = $actividad->idActividadMejora;
        $data['codigo'] = $codigo;

        $plan = PlanCorrectivo::create($data);

        // Usar el array 'actividades' que viene del frontend
        if ($request->has('actividades') && is_array($request->actividades)) {
            foreach ($request->actividades as $act) {
                $actividadData = [
                    'idPlanCorrectivo' => $plan->idPlanCorrectivo,
                    'descripcionAct' => $act['descripcionAct'] ?? null,
                    'responsable' => $act['responsable'] ?? '',
                    'fechaProgramada' => $act['fechaProgramada'] ?? null,
                    'tipo' => $act['tipo'] ?? 'planaccion'
                ];
                ActividadPlan::create($actividadData);
            }
        }

        return response()->json($plan->load('actividades'), 201);
    }

    public function show($id)
    {
        $plan = PlanCorrectivo::with('actividades')->find($id);
        if (!$plan) {
            return response()->json(['message' => 'Plan no encontrado'], 404);
        }
        return response()->json($plan);
    }

    public function update(Request $request, $id)
    {
        $plan = PlanCorrectivo::find($id);
        if (!$plan) {
            return response()->json(['message' => 'Plan no encontrado'], 404);
        }

        // Actualizamos el plan principal
        $plan->update($request->all());

        // Eliminamos las actividades actuales asociadas al plan
        $plan->actividades()->delete();

        // Usar el array 'actividades' que viene del frontend
        if ($request->has('actividades') && is_array($request->actividades)) {
            foreach ($request->actividades as $act) {
                $actividadData = [
                    'idPlanCorrectivo' => $plan->idPlanCorrectivo,
                    'descripcionAct' => $act['descripcionAct'] ?? null,
                    'responsable' => $act['responsable'] ?? '',
                    'fechaProgramada' => $act['fechaProgramada'] ?? null,
                    'tipo' => $act['tipo'] ?? 'planaccion'
                ];
                ActividadPlan::create($actividadData);
            }
        }

        return response()->json($plan->load('actividades'));
    }

    public function destroy($id)
    {
        $plan = PlanCorrectivo::find($id);
        if (!$plan) {
            return response()->json(['message' => 'Plan no encontrado'], 404);
        }
        $plan->delete();
        return response()->json(['message' => 'Plan eliminado correctamente'], 204);
    }

    //Funciones de las actvidades

    public function createActividad(Request $request)
    {
        $actividad = ActividadPlan::create($request->all());
        return response()->json($actividad, 201);
    }

    public function updateActividad(Request $request, $idActividadPlan)
    {
        $actividad = ActividadPlan::find($idActividadPlan);
        if (!$actividad) {
            return response()->json(['message' => 'Actividad no encontrada'], 404);
        }
        $actividad->update($request->all());
        return response()->json($actividad);
    }

    public function deleteActividad($idActividadPlan)
    {
        $actividad = ActividadPlan::find($idActividadPlan);
        if (!$actividad) {
            return response()->json(['message' => 'Actividad no encontrada'], 404);
        }
        $actividad->delete();
        return response()->json(['message' => 'Actividad eliminada']);
    }

    public function getByIdRegistro($idRegistro)
    {
        $planes = PlanCorrectivo::with('actividades')
            ->whereHas('actividadMejora', function ($query) use ($idRegistro) {
                $query->where('idRegistro', $idRegistro);
            })->get();

        if ($planes->isEmpty()) {
            return response()->json(['message' => 'No se encontraron planes de acción para este registro.'], 404);
        }

        return response()->json($planes);
    }

}
