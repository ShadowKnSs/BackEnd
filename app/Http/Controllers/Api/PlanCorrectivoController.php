<?php

namespace App\Http\Controllers\ApI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanCorrectivo;
use App\Models\ActividadPlan;

class PlanCorrectivoController extends Controller
{
    public function index(){
        //Nombre de la variable que va almacenar la estructura <$nombre>
        // Se iguala a la tabla principal
        // La función with('actividades') hace que Laravel cargue las actividades relacionadas con cada plan correctivo "Actvidiades" es el nombre de la funcion de la FK
        // Con get() obtiene todos los planes correctivos con sus actividades relacionadas
        $planes = PlanCorrectivo::with('actividades') -> get();
        return response()->json($planes);
    }

    public function store(Request $request){
        $request->validate([
            'fechaInicio'          => 'required|date',
            'origenConformidad'    => 'required|string|max:510',
            'equipoMejora'         => 'required|string|max:255',
            'requisito'            => 'required|string|max:510',
            'incumplimiento'       => 'required|string|max:510',
            'evidencia'            => 'required|string|max:510',
            'coordinadorPlan'      => 'required|string|max:255'
        ]);
    
        $plan = PlanCorrectivo::create($request->all());
    
        // Si se envían actividades de reacción, guardarlas
        if($request->has('reaccion')){
            foreach ($request->input('reaccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'reaccion';
                ActividadPlan::create($act);
            }
        }
        
        // Si se envían actividades del plan de acción, guardarlas
        if($request->has('planAccion')){
            foreach ($request->input('planAccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'planaccion';
                ActividadPlan::create($act);
            }
        }
    
        return response()->json($plan, 201);
    }
    

    public function show($id){
        $plan = PlanCorrectivo::with('actividades')->find($id);
        if(!$plan){
            return response()->json(['message' => 'Plan no encontrado'], 404);
        }
        return response()->json($plan);
    }

    public function update(Request $request, $id){
        $plan = PlanCorrectivo::find($id);
        if(!$plan){
            return response()->json(['message' => 'Plan no encontrado'], 404);
        }
        
        // Actualizamos el plan principal
        $plan->update($request->all());
        
        // Eliminamos las actividades actuales asociadas al plan
        $plan->actividades()->delete();
        
        // Creamos las nuevas actividades de reacción
        if($request->has('reaccion')){
            foreach ($request->input('reaccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                // Mapeamos el campo 'actividad' al campo 'descripcionAct'
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'reaccion';
                ActividadPlan::create($act);
            }
        }
        
        // Creamos las nuevas actividades del plan de acción
        if($request->has('planAccion')){
            foreach ($request->input('planAccion') as $act) {
                $act['idPlanCorrectivo'] = $plan->idPlanCorrectivo;
                $act['descripcionAct'] = isset($act['actividad']) ? $act['actividad'] : null;
                $act['tipo'] = 'planaccion';
                ActividadPlan::create($act);
            }
        }
        
        return response()->json($plan);
    }

    public function destroy($id){
        $plan = PlanCorrectivo::find($id);
        if(!$plan){
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

    public function getByRegistro($idRegistro)
{
    // Buscar los planes correctivos donde idRegistro coincida, incluyendo las actividades asociadas
    $plans = PlanCorrectivo::with('actividades')->where('idRegistro', $idRegistro)->get();

    if ($plans->isEmpty()) {
        return response()->json(['message' => 'No se encontraron planes de acción para este registro.'], 404);
    }
    return response()->json($plans);
}
}
