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
use Illuminate\Support\Facades\Log;

class FuentePtController extends Controller
{
    public function index($id)
    {
        $plan = PlanTrabajo::with('fuentes')->findOrFail($id);
        return response()->json($plan->fuentes);
    }

    public function store(Request $request, $id)
    {
        // Validación: sin max:255 para campos TEXT
        $request->validate([
            'fuentes' => 'required|array|min:1',
            'fuentes.*.responsable' => 'required|string|max:255',
            'fuentes.*.fechaInicio' => 'required|date',
            // dentro del mismo item, referimos el nombre simple:
            'fuentes.*.fechaTermino' => 'required|date|after_or_equal:fechaInicio',
            'fuentes.*.estado' => 'required|in:En proceso,Cerrado',
            'fuentes.*.nombreFuente' => 'required|string|max:255',
            'fuentes.*.elementoEntrada' => 'required|string',   // TEXT
            'fuentes.*.descripcion' => 'required|string',       // TEXT
            'fuentes.*.entregable' => 'required|string',        // TEXT
            // 'fuentes.*.noActividad' => 'sometimes|integer|min:1', // si lo envías desde el front
            // 'fuentes.*.numero' => 'sometimes|integer|min:1',       // opcional, si existe la columna
        ]);

        DB::transaction(function () use ($request, $id) {
            $plan = PlanTrabajo::findOrFail($id);

            // Si prefieres no borrar todo para no romper vínculos, quita esta línea.
            FuentePt::where('idPlanTrabajo', $plan->idPlanTrabajo)->delete();

            foreach ($request->fuentes as $f) {
                // 1) NoActividad: tomar del payload o calcular secuencial
                $siguiente = (int)(FuentePt::where('idPlanTrabajo', $plan->idPlanTrabajo)->max('noActividad') ?? 0) + 1;
                $noActividad = $f['noActividad'] ?? $f['numero'] ?? $siguiente;
                $ptCode = 'PT-' . str_pad($noActividad, 2, '0', STR_PAD_LEFT);

                // 2) Crear la fuente con noActividad siempre presente
                $payload = [
                    'idPlanTrabajo'   => $plan->idPlanTrabajo,
                    'noActividad'     => $noActividad,
                    'responsable'     => $f['responsable'],
                    'fechaInicio'     => $f['fechaInicio'],
                    'fechaTermino'    => $f['fechaTermino'],
                    'estado'          => $f['estado'],
                    'nombreFuente'    => $f['nombreFuente'],
                    'elementoEntrada' => $f['elementoEntrada'],
                    'descripcion'     => $f['descripcion'],
                    'entregable'      => $f['entregable'],
                ];
                // (Opcional) si tu tabla también tiene columna 'numero':
                if (\Schema::hasColumn('fuentept', 'numero')) {
                    $payload['numero'] = $noActividad;
                }

                $nuevaFuente = FuentePt::create($payload);

                // === Vincular/propagar a RIESGOS ===
                // Ubicar idGesRies para el mismo proceso/año del plan (actividadMejora -> registro -> proceso|año)
                $actividad = ActividadMejora::find($plan->idActividadMejora);
                if (!$actividad) continue;

                $registroBase = Registros::find($actividad->idRegistro);
                if (!$registroBase) continue;

                $registroGR = Registros::where('idProceso', $registroBase->idProceso)
                    ->where('año', $registroBase->año)
                    ->whereIn('Apartado', ['Gestión de Riesgo', 'Gestion de Riesgo'])
                    ->first();
                if (!$registroGR) continue;

                $gestion = GestionRiesgos::where('idRegistro', $registroGR->idRegistro)->first();
                if (!$gestion) continue;

                $idGesRies = $gestion->idGesRies;

                // Buscar riesgo por idFuente (ideal) o por descripción de entrada (fallback)
                $riesgo = Riesgo::where('idFuente', $nuevaFuente->idFuente)->first();
                if (!$riesgo) {
                    $riesgo = Riesgo::where('idGesRies', $idGesRies)
                        ->where('descripcion', $f['elementoEntrada'])
                        ->first();
                }

                if ($riesgo) {
                    // Actualizar riesgo existente
                    $riesgo->update([
                        'idFuente'    => $nuevaFuente->idFuente,
                        'actividades' => $f['descripcion'],
                        'responsable' => $f['responsable'],
                        'accionMejora'=> $ptCode,
                        'fechaImp'    => $f['fechaInicio'],
                        'fechaEva'    => $f['fechaTermino'],
                    ]);
                } else {
                    // Crear riesgo nuevo
                    Riesgo::create([
                        'idGesRies'   => $idGesRies,
                        'idFuente'    => $nuevaFuente->idFuente,
                        'descripcion' => $f['elementoEntrada'],
                        'actividades' => $f['descripcion'],
                        'accionMejora'=> $ptCode,
                        'responsable' => $f['responsable'],
                        'fechaImp'    => $f['fechaInicio'],
                        'fechaEva'    => $f['fechaTermino'],
                        // Valores mínimos por si tu modelo exige enteros
                        'valorSeveridad'   => 1,
                        'valorOcurrencia'  => 1,
                        'valorNRP'         => 1,
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

    Log::info("[FuentePt@update] Datos recibidos:", $request->all());

    // 1) Normalizar: si viene como fuentes[0], aplanar a payload plano
    $payload = $request->all();
    if (isset($payload['fuentes']) && is_array($payload['fuentes']) && isset($payload['fuentes'][0]) && is_array($payload['fuentes'][0])) {
        $payload = array_merge($payload, $payload['fuentes'][0]);
    }

    // 2) Validación: textos largos con tope 512 (o sin tope si tus columnas son TEXT)
    $validator = Validator::make($payload, [
        'responsable'     => 'sometimes|required|string|max:255',
        'fechaInicio'     => 'sometimes|required|date',
        'fechaTermino'    => 'sometimes|required|date|after_or_equal:fechaInicio',
        'estado'          => 'sometimes|required|in:En proceso,Cerrado',
        'nombreFuente'    => 'sometimes|required|string|max:255',
        'elementoEntrada' => 'sometimes|required|string|max:512',
        'descripcion'     => 'sometimes|required|string|max:512',
        'entregable'      => 'sometimes|required|string|max:512',
        'noActividad'     => 'required|integer|min:1',
    ]);
    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // 3) Actualizar Fuente (evita cambiar noActividad si no corresponde)
    $fuente->update(collect($payload)->except('noActividad','fuentes')->toArray());

    // 4) PT-XX y sincronía con RIESGOS
    $ptCode = 'PT-' . str_pad($payload['noActividad'], 2, '0', STR_PAD_LEFT);

    $riesgo = Riesgo::where('idFuente', $fuente->idFuente)->first();
    if ($riesgo) {
        $riesgo->update([
            'descripcion'  => $payload['elementoEntrada'] ?? $riesgo->descripcion,
            'actividades'  => $payload['descripcion']     ?? $riesgo->actividades,
            'responsable'  => $payload['responsable']     ?? $riesgo->responsable,
            'accionMejora' => $ptCode,
            'fechaImp'     => $payload['fechaInicio']     ?? $riesgo->fechaImp,
            'fechaEva'     => $payload['fechaTermino']    ?? $riesgo->fechaEva,
        ]);
    }

    return response()->json([
        'message' => 'Fuente actualizada exitosamente',
        'fuente'  => $fuente
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
