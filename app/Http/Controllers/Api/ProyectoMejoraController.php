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

        // Validación de datos recibidos
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

            // Buscar la actividad de mejora vinculada a ese registro
            $actividad = ActividadMejora::where('idRegistro', $data['idRegistro'])->first();
            if (!$actividad) {
                Log::warning('No se encontró ActividadMejora para idRegistro', ['idRegistro' => $data['idRegistro']]);
                return response()->json([
                    'message' => 'No se encontró una ActividadMejora para el idRegistro proporcionado.'
                ], 400);
            }
            // Crear el registro principal en la tabla proyectomejora
            Log::info('Creando ProyectoMejora para actividad', ['idActividadMejora' => $actividad->idActividadMejora]);
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
            Log::info('Proyecto creado', ['idProyectoMejora' => $proyecto->idProyectoMejora]);


            // Insertar cada objetivo (si se proporcionan)
            if (isset($data['objetivos'])) {
                foreach ($data['objetivos'] as $obj) {
                    Objetivo::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'descripcionObj' => $obj['descripcion'] ?? null,
                    ]);
                }
            }

            // Insertar responsables
            if (isset($data['responsables'])) {
                foreach ($data['responsables'] as $resp) {
                    ResponsableInv::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'nombre' => $resp['nombre'] ?? null,
                    ]);
                }
            }

            // Insertar indicadores de éxito
            if (isset($data['indicadoresExito'])) {
                foreach ($data['indicadoresExito'] as $ind) {
                    IndicadoresExito::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'nombreInd' => $ind['nombre'] ?? null,
                    ]);
                }
            }

            // Insertar recursos
            if (isset($data['recursos'])) {
                foreach ($data['recursos'] as $rec) {
                    Recurso::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'tiempoEstimado' => $rec['tiempoEstimado'] ?? null,
                        'recursosMatHum' => $rec['recursosMatHum'] ?? null,
                        'costo' => 0
                    ]);
                }
            }

            // Insertar plan de trabajo (actividades)
            if (isset($data['actividadesPM'])) {
                foreach ($data['actividadesPM'] as $act) {
                    ActividadesPM::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'descripcionAct' => $act['actividad'] ?? null,
                        'responsable' => $act['responsable'] ?? null,
                        'fecha' => $act['fecha'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Proyecto guardado correctamente',
                'data' => $proyecto
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store ProyectoMejora', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error al guardar el proyecto',
                'error' => $e->getMessage()
            ], 500);
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
            'objetivos' => 'nullable|array',
            'objetivos.*.descripcion' => 'nullable|string',
            'areaImpacto' => 'nullable|string',
            'personalBeneficiado' => 'nullable|string',
            'responsables' => 'nullable|array',
            'responsables.*.nombre' => 'nullable|string',
            'situacionActual' => 'nullable|string',
            'indicadoresExito' => 'nullable|array',
            'indicadoresExito.*.nombre' => 'nullable|string',
            'recursos' => 'nullable|array',
            'recursos.*.tiempoEstimado' => 'nullable|string',
            'recursos.*.recursosMatHum' => 'nullable|string',
            'recursos.*.costo' => 'nullable|numeric',
            'actividadesPM' => 'nullable|array',
            'actividadesPM.*.actividad' => 'nullable|string',
            'actividadesPM.*.responsable' => 'nullable|string',
            'actividadesPM.*.fecha' => 'nullable|date',
            'aprobacionNombre' => 'nullable|string',
            'aprobacionPuesto' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
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

            // Reinsertar nuevos datos
            foreach ($data['objetivos'] ?? [] as $obj) {
                Objetivo::create([
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'descripcionObj' => $obj['descripcion'] ?? null,
                ]);
            }

            foreach ($data['responsables'] ?? [] as $resp) {
                ResponsableInv::create([
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'nombre' => $resp['nombre'] ?? null,
                ]);
            }

            foreach ($data['indicadoresExito'] ?? [] as $ind) {
                IndicadoresExito::create([
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'nombreInd' => $ind['nombre'] ?? null,
                    'meta' => $ind['meta'] ?? null,
                ]);
            }

            foreach ($data['recursos'] ?? [] as $rec) {
                Recurso::create([
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'tiempoEstimado' => $rec['tiempoEstimado'] ?? null,
                    'recursosMatHum' => $rec['recursosMatHum'] ?? null,
                    'costo' => $rec['costo'] ?? 0,
                ]);
            }

            foreach ($data['actividadesPM'] ?? [] as $act) {
                ActividadesPM::create([
                    'idProyectoMejora' => $proyecto->idProyectoMejora,
                    'descripcionAct' => $act['actividad'] ?? null,
                    'responsable' => $act['responsable'] ?? null,
                    'fecha' => $act['fecha'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Proyecto actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
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
            // Eliminar en cascada
            $proyecto->objetivos()->delete();
            $proyecto->responsablesInv()->delete();
            $proyecto->indicadoresExito()->delete();
            $proyecto->recursos()->delete();
            $proyecto->actividades()->delete();

            // Eliminar el proyecto
            $proyecto->delete();

            DB::commit();
            return response()->json(['message' => 'Proyecto eliminado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }

    public function getByRegistro($idRegistro)
    {
        $actividad = ActividadMejora::where('idRegistro', $idRegistro)->first();

        if (!$actividad) {
            return response()->json(['message' => 'No se encontró actividad'], 404);
        }

        $proyectos = ProyectoMejora::where('idActividadMejora', $actividad->idActividadMejora)
            ->with(['objetivos', 'responsablesInv', 'indicadoresExito', 'recursos', 'actividades'])
            ->get();

        return response()->json($proyectos);
    }

}
