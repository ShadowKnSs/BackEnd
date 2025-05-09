<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FuentePt;
use App\Models\PlanTrabajo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FuentePtController extends Controller
{
    public function index($id)
    {
        $plan = PlanTrabajo::with('fuentes')->findOrFail($id);
        return response()->json($plan->fuentes);
    }


    public function store(Request $request, $id)
    {
        $request->validate([
            'fuentes' => 'required|array|min:1',
            'fuentes.*.responsable' => 'required|string|max:255',
            'fuentes.*.fechaInicio' => 'required|date',
            'fuentes.*.fechaTermino' => 'required|date|after_or_equal:fuentes.*.fechaInicio',
            'fuentes.*.estado' => 'required|in:En proceso,Cerrado',
            'fuentes.*.nombreFuente' => 'required|string|max:255',
            'fuentes.*.elementoEntrada' => 'required|string',
            'fuentes.*.descripcion' => 'required|string|max:255',
            'fuentes.*.entregable' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {
            $plan = PlanTrabajo::findOrFail($id);
            FuentePt::where('idPlanTrabajo', $plan->idPlanTrabajo)->delete();

            foreach ($request->fuentes as $fuente) {
                $fuente['idPlanTrabajo'] = $plan->idPlanTrabajo;
                FuentePt::create($fuente);
            }
        });

        return response()->json([
            'message' => 'Fuentes guardadas correctamente',
            'planTrabajo' => PlanTrabajo::with('fuentes')->find($id)
        ]);
    }

    public function show($id)
    {
        $fuente = FuentePt::find($id);
        if (!$fuente) {
            return response()->json(['message' => 'Fuente no encontrada'], 404);
        }
        return response()->json($fuente, 200);
    }

    public function update(Request $request, $id)
    {
        $fuente = FuentePt::find($id);
        if (!$fuente) {
            return response()->json(['message' => 'Fuente no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'responsable' => 'sometimes|required|string|max:255',
            'fechaInicio' => 'sometimes|required|date',
            'fechaTermino' => 'sometimes|required|date',
            'estado' => 'sometimes|required|in:En proceso,Cerrado',
            'nombreFuente' => 'sometimes|required|string|max:255',
            'elementoEntrada' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string|max:255',
            'entregable' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $fuente->update($request->all());
        return response()->json([
            'message' => 'Fuente actualizada exitosamente',
            'fuente' => $fuente
        ], 200);
    }

    public function destroy($id)
    {
        $fuente = FuentePt::find($id);
        if (!$fuente) {
            return response()->json(['message' => 'Fuente no encontrada'], 404);
        }
        $fuente->delete();
        return response()->json(['message' => 'Fuente eliminada exitosamente'], 200);
    }
}
