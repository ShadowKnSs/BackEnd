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
            'origenConformidad' => 'required|string|',
            'equipoMejora' => 'required|string|',
            'requisito' => 'required|string|',
            'incumplimiento' => 'required|string|',
            'evidencia' => 'required|string|',
            'coordinadorPlan' => 'required|string'
        ]);


        // 🔍 Buscar idActividadMejora relacionado al idRegistro
        $actividad = ActividadMejora::where('idRegistro', $request->idRegistro)->first();

        if (!$actividad) {
            return response()->json(['error' => 'No se encontró ActividadMejora asociada'], 422);
        }

        // Obtener el último número de secuencia para este año
    $year = date('y');
    $lastPlan = PlanCorrectivo::where('codigo', 'like', "PAC-%-$year")
        ->orderBy('codigo', 'desc')
        ->first();

    $nextNumber = 1;
    if ($lastPlan) {
        // Extraer el número del código (ej: PAC-03-25 → 03)
        preg_match('/PAC-(\d{2})-\d{2}/', $lastPlan->codigo, $matches);
        if (isset($matches[1])) {
            $nextNumber = (int)$matches[1] + 1;
        }
    }

    $codigo = "PAC-" . str_pad($nextNumber, 2, '0', STR_PAD_LEFT) . "-" . $year;

    // ✅ Agregar manualmente el campo
    $data = $request->all();
    $data['idActividadMejora'] = $actividad->idActividadMejora;
    $data['codigo'] = $codigo;

    $plan = PlanCorrectivo::create($data);
    

        // Si se envían actividades de reacción, guardarlas
        if ($request->has('reaccion')) {
            foreach ($request->input('reaccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'reaccion';
                ActividadPlan::create($act);
            }
        }

        // Si se envían actividades del plan de acción, guardarlas
        if ($request->has('planAccion')) {
            foreach ($request->input('planAccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'planaccion';
                ActividadPlan::create($act);
            }
        }

        return response()->json($plan, 201);
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

        // Creamos las nuevas actividades de reacción
        if ($request->has('reaccion')) {
            foreach ($request->input('reaccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                // Mapeamos el campo 'actividad' al campo 'descripcionAct'
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'reaccion';
                ActividadPlan::create($act);
            }
        }

        // Creamos las nuevas actividades del plan de acción
        if ($request->has('planAccion')) {
            foreach ($request->input('planAccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'planaccion';
                ActividadPlan::create($act);
            }
        }

        return response()->json($plan);
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
