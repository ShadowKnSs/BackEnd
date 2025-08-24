<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ProyectoMejora;
use App\Models\Objetivo;
use App\Models\ResponsableInv;
use App\Models\IndicadoresExito;
use App\Models\Recurso;
use App\Models\ActividadesPM;
use App\Models\ActividadMejora;
use Illuminate\Support\Facades\Log;

class ProyectoMejoraController extends Controller
{
    /**
     * Guarda un proyecto de mejora y sus registros asociados.
     */
    public function store(Request $request)
    {
        Log::info('Inicio de store ProyectoMejora', ['payload' => $request->all()]);

        $data = $request->validate([
            'idRegistro' => 'required|integer',
            'division' => 'required|string',
            'departamento' => 'required|string',
            'fecha' => 'nullable|date',
            'noMejora' => 'nullable|integer',
            'responsable' => 'nullable|string',
            'descripcionMejora' => 'nullable|string',
            'objetivos' => 'nullable|array',
            'objetivos.*.descripcion' => 'nullable|string',
            'areaImpacto' => 'nullable|string',
            'personalBeneficiado' => 'nullable|string',
            'responsables' => 'nullable|array',
            'responsables.*.nombre' => 'nullable|string',
            'situacionActual' => 'nullable|string',
            'indicadoresExito' => 'nullable|array',
            'indicadoresExito.*.nombre' => 'nullable|string',
            'indicadoresExito.*.meta' => 'nullable|numeric',
            'recursos' => 'nullable|array',
            'recursos.*.tiempoEstimado' => 'nullable|string',
            'recursos.*.recursosMatHum' => 'nullable|string',
            'costoProyecto' => 'nullable|numeric',
            'actividadesPM' => 'nullable|array',
            'actividadesPM.*.actividad' => 'nullable|string',
            'actividadesPM.*.responsable' => 'nullable|string',
            'actividadesPM.*.fecha' => 'nullable|date',
            'aprobacionNombre' => 'nullable|string',
            'aprobacionPuesto' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $actividad = ActividadMejora::where('idRegistro', $data['idRegistro'])->first();
            if (!$actividad) {
                return response()->json(['message' => 'No se encontró una ActividadMejora para el idRegistro.'], 400);
            }

            $proyecto = ProyectoMejora::create([
                'idActividadMejora' => $actividad->idActividadMejora,
                'division' => $data['division'],
                'departamento' => $data['departamento'],
                'responsable' => $data['responsable'] ?? null,
                'fecha' => $data['fecha'] ?? null,
                'noMejora' => $data['noMejora'] ?? null,
                'descripcionMejora' => $data['descripcionMejora'] ?? null,
                'areaImpacto' => $data['areaImpacto'] ?? null,
                'personalBeneficiado' => $data['personalBeneficiado'] ?? null,
                'situacionActual' => $data['situacionActual'] ?? null,
                'aprobacionNombre' => $data['aprobacionNombre'] ?? null,
                'aprobacionPuesto' => $data['aprobacionPuesto'] ?? null,
            ]);

            if (!empty($data['objetivos'])) {
                $objetivos = array_map(fn($obj) => [
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'descripcionObj' => $obj['descripcion'] ?? null
                ], $data['objetivos']);
                Objetivo::insert($objetivos);
            }

            if (!empty($data['responsables'])) {
                $responsables = array_map(fn($r) => [
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'nombre' => $r['nombre'] ?? null
                ], $data['responsables']);
                ResponsableInv::insert($responsables);
            }

            if (!empty($data['indicadoresExito'])) {
                $indicadores = array_map(fn($i) => [
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'nombreInd' => $i['nombre'] ?? null,
                    'meta' => $i['meta'] ?? null,
                ], $data['indicadoresExito']);
                IndicadoresExito::insert($indicadores);
            }


            if (!empty($data['recursos'])) {
                $recursos = array_map(fn($r) => [
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'tiempoEstimado' => $r['tiempoEstimado'] ?? null,
                    'recursosMatHum' => $r['recursosMatHum'] ?? null,
                    'costo' => 0
                ], $data['recursos']);
                Recurso::insert($recursos);
            }

            if (!empty($data['actividadesPM'])) {
                $actividades = array_map(fn($a) => [
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'descripcionAct' => $a['actividad'] ?? null,
                    'responsable' => $a['responsable'] ?? null,
                    'fecha' => $a['fecha'] ?? null
                ], $data['actividadesPM']);
                ActividadesPM::insert($actividades);
            }

            DB::commit();
            return response()->json(['message' => 'Proyecto guardado correctamente', 'data' => $proyecto], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store ProyectoMejora', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al guardar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra la información de un proyecto de mejora por su ID, incluyendo sus relaciones.
     */
    public function show($id)
    {
        // Se asume que en el modelo ProyectoMejora definiste las relaciones:
        // objetivos, responsablePer, indicadoresExito, recursos y actividadesPM.
        $proyecto = ProyectoMejora::with([
            'objetivos',
            'responsablePer',
            'indicadoresExito',
            'recursos',
            'actividadesPM'
        ])->find($id);

        if (!$proyecto) {
            return response()->json([
                'message' => 'Proyecto no encontrado'
            ], 404);
        }

        return response()->json([
            'data' => $proyecto
        ]);
    }

    public function index()
    {
        $proyectos = ProyectoMejora::select('idProyectoMejora', 'descripcionMejora', 'fecha', 'noMejora')
            ->orderByDesc('fecha')
            ->get();

        return response()->json($proyectos);
    }

    public function update(Request $request, $id)
    {
        $proyecto = ProyectoMejora::find($id);
        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $data = $request->validate([
            'division' => 'required|string',
            'departamento' => 'required|string',
            'fecha' => 'nullable|date',
            'noMejora' => 'nullable|integer',
            'responsable' => 'nullable|string',
            'descripcionMejora' => 'nullable|string',
            'areaImpacto' => 'nullable|string',
            'personalBeneficiado' => 'nullable|string',
            'situacionActual' => 'nullable|string',
            'aprobacionNombre' => 'nullable|string',
            'aprobacionPuesto' => 'nullable|string',
            'objetivos' => 'nullable|array',
            'objetivos.*.descripcion' => 'nullable|string',
            'responsables' => 'nullable|array',
            'responsables.*.nombre' => 'nullable|string',
            'indicadoresExito' => 'nullable|array',
            'indicadoresExito.*.nombre' => 'nullable|string',
            'indicadoresExito.*.meta' => 'nullable|numeric',
            'recursos' => 'nullable|array',
            'recursos.*.tiempoEstimado' => 'nullable|string',
            'recursos.*.recursosMatHum' => 'nullable|string',
            'recursos.*.costo' => 'nullable|numeric',
            'actividadesPM' => 'nullable|array',
            'actividadesPM.*.actividad' => 'nullable|string',
            'actividadesPM.*.responsable' => 'nullable|string',
            'actividadesPM.*.fecha' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Update principal
            $proyecto->update([
                'division' => $data['division'],
                'departamento' => $data['departamento'],
                'responsable' => $data['responsable'] ?? null,
                'fecha' => $data['fecha'] ?? null,
                'noMejora' => $data['noMejora'] ?? null,
                'descripcionMejora' => $data['descripcionMejora'] ?? null,
                'areaImpacto' => $data['areaImpacto'] ?? null,
                'personalBeneficiado' => $data['personalBeneficiado'] ?? null,
                'situacionActual' => $data['situacionActual'] ?? null,
                'aprobacionNombre' => $data['aprobacionNombre'] ?? null,
                'aprobacionPuesto' => $data['aprobacionPuesto'] ?? null,
            ]);

            // Eliminar relaciones anteriores
            $proyecto->objetivos()->delete();
            $proyecto->responsablesInv()->delete();
            $proyecto->indicadoresExito()->delete();
            $proyecto->recursos()->delete();
            $proyecto->actividades()->delete();

            $id = $proyecto->idProyectoMejora;

            // Reinsertar relaciones (con insert masivo si hay datos)
            if (!empty($data['objetivos'])) {
                Objetivo::insert(array_map(fn($o) => [
                    'idProyectoMejora' => $id,
                    'descripcionObj' => $o['descripcion'] ?? null,
                ], $data['objetivos']));
            }

            if (!empty($data['responsables'])) {
                ResponsableInv::insert(array_map(fn($r) => [
                    'idProyectoMejora' => $id,
                    'nombre' => $r['nombre'] ?? null,
                ], $data['responsables']));
            }

            if (!empty($data['indicadoresExito'])) {
                IndicadoresExito::insert(array_map(fn($i) => [
                    'idProyectoMejora' => $id,
                    'nombreInd' => $i['nombre'] ?? null,
                    'meta' => $i['meta'] ?? null,
                ], $data['indicadoresExito']));
            }

            if (!empty($data['recursos'])) {
                Recurso::insert(array_map(fn($r) => [
                    'idProyectoMejora' => $id,
                    'tiempoEstimado' => $r['tiempoEstimado'] ?? null,
                    'recursosMatHum' => $r['recursosMatHum'] ?? null,
                    'costo' => $r['costo'] ?? 0,
                ], $data['recursos']));
            }

            if (!empty($data['actividadesPM'])) {
                ActividadesPM::insert(array_map(fn($a) => [
                    'idProyectoMejora' => $id,
                    'descripcionAct' => $a['actividad'] ?? null,
                    'responsable' => $a['responsable'] ?? null,
                    'fecha' => $a['fecha'] ?? null,
                ], $data['actividadesPM']));
            }

            DB::commit();
            return response()->json(['message' => 'Proyecto actualizado correctamente'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en update ProyectoMejora', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al actualizar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        $proyecto = ProyectoMejora::find($id);
        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $id = $proyecto->idProyectoMejora;

            // Eliminación en bloque por relaciones
            Objetivo::where('idProyectoMejora', $id)->delete();
            ResponsableInv::where('idProyectoMejora', $id)->delete();
            IndicadoresExito::where('idProyectoMejora', $id)->delete();
            Recurso::where('idProyectoMejora', $id)->delete();
            ActividadesPM::where('idProyectoMejora', $id)->delete();

            $proyecto->delete();

            DB::commit();
            return response()->json(['message' => 'Proyecto eliminado correctamente'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en destroy ProyectoMejora', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al eliminar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }


    public function getByRegistro($idRegistro)
    {
        $actividad = ActividadMejora::select('idActividadMejora')
            ->where('idRegistro', $idRegistro)
            ->first();

        if (!$actividad) {
            return response()->json(['message' => 'No se encontró actividad'], 404);
        }

        $proyectos = ProyectoMejora::with([
            'objetivos',
            'responsablesInv',
            'indicadoresExito',
            'recursos',
            'actividades'
        ])
            ->where('idActividadMejora', $actividad->idActividadMejora)
            ->orderByDesc('fecha')
            ->get();

        return response()->json($proyectos);
    }


}
