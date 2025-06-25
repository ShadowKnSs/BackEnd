<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FuentePt;
use App\Models\PlanTrabajo;
use App\Models\ActividadMejora;
use App\Models\Riesgo;
use App\Models\GestionRiesgos;
use App\Models\Registros;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Log;

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
                $nuevaFuente = FuentePt::create($fuente);

                // === Obtener idGesRies ===
                $actividad = ActividadMejora::find($plan->idActividadMejora);
                if (!$actividad)
                    continue;

                $registroBase = Registros::find($actividad->idRegistro);
                if (!$registroBase)
                    continue;

                $registroGR = Registros::where('idProceso', $registroBase->idProceso)
                    ->where('año', $registroBase->año)
                    ->where('Apartado', 'Gestión de Riesgo')
                    ->first();

                if (!$registroGR)
                    continue;

                $gestion = GestionRiesgos::where('idRegistro', $registroGR->idRegistro)->first();
                if (!$gestion)
                    continue;

                $idGesRies = $gestion->idGesRies;

                // === Buscar riesgo existente por descripcion = fuente.elementoEntrada
                $riesgo = Riesgo::where('idGesRies', $idGesRies)
                    ->where('descripcion', $fuente['elementoEntrada'])
                    ->first();

                if ($riesgo) {
                    // Actualizar el riesgo existente
                    $riesgo->update([
                        'actividades' => $fuente['descripcion'],
                        'responsable' => $fuente['responsable'],
                    ]);
                } else {
                    // Generar PT-XX secuencial
                    $ptCount = Riesgo::where('idGesRies', $idGesRies)
                        ->where('accionMejora', 'like', 'PT-%')
                        ->count();
                    $numeroPT = str_pad($ptCount + 1, 2, '0', STR_PAD_LEFT);

                    // Crear nuevo riesgo
                    Riesgo::create([
                        'idGesRies' => $idGesRies,
                        'descripcion' => $fuente['elementoEntrada'],
                        'actividades' => $fuente['descripcion'],
                        'accionMejora' => "PT-$numeroPT",
                        'responsable' => $fuente['responsable'],
                    ]);
                }
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
        Log::info("[update] Datos recibidos en fuente:", $request->all());


        $validator = Validator::make($request->all(), [
            'responsable' => 'sometimes|required|string|max:255',
            'fechaInicio' => 'sometimes|required|date',
            'fechaTermino' => 'sometimes|required|date',
            'estado' => 'sometimes|required|in:En proceso,Cerrado',
            'nombreFuente' => 'sometimes|required|string|max:255',
            'elementoEntrada' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string|max:255',
            'entregable' => 'sometimes|required|string|max:255',
            'noActividad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $fuente->update($request->except('noActividad'));

        // === Buscar el riesgo asociado ===
        $riesgo = Riesgo::where('idFuente', $fuente->idFuente)->first();
        if ($riesgo) {
            $riesgo->update([
                'responsable' => $request->responsable,
                'actividades' => $request->descripcion,
                'accionMejora' => 'PT-' . str_pad($request->noActividad, 2, '0', STR_PAD_LEFT),
            ]);
        }

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
