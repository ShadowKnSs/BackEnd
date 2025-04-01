<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ProyectoMejora;
use App\Models\Objetivo;
use App\Models\ResponsablePer;
use App\Models\IndicadoresExito;
use App\Models\Recurso;
use App\Models\ActividadesPM;

class ProyectoMejoraController extends Controller
{
    /**
     * Guarda un proyecto de mejora y sus registros asociados.
     */
    public function store(Request $request)
    {
        // Validación de datos recibidos
        $data = $request->validate([
            'division'                     => 'required|string',
            'departamento'                 => 'required|string',
            'fecha'                        => 'nullable|date',
            'noMejora'                     => 'nullable|integer',
            'responsable'                  => 'nullable|string',
            'descripcionMejora'            => 'nullable|string',
            'objetivos'                    => 'nullable|array',
            'objetivos.*.descripcion'      => 'nullable|string',
            'areaImpacto'                  => 'nullable|string',
            'personalBeneficiado'          => 'nullable|integer',
            'responsables'                 => 'nullable|array',
            'responsables.*.nombre'        => 'nullable|string',
            'situacionActual'              => 'nullable|string',
            'indicadoresExito'             => 'nullable|array',
            'indicadoresExito.*.nombre'    => 'nullable|string',
            'recursos'                     => 'nullable|array',
            'recursos.*.tiempoEstimado'      => 'nullable|string',
            'recursos.*.recursosMatHum'     => 'nullable|string',
            'costoProyecto'                => 'nullable|numeric',
            'actividadesPM'                => 'nullable|array',
            'actividadesPM.*.actividad'    => 'nullable|string',
            'actividadesPM.*.responsable'  => 'nullable|string',
            'actividadesPM.*.fecha'        => 'nullable|date',
            'aprobacionNombre'             => 'nullable|string',
            'aprobacionPuesto'             => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Crear el registro principal en la tabla proyectomejora
            $proyecto = ProyectoMejora::create([
                'responsable'        => $data['responsable'] ?? null,
                'fecha'              => $data['fecha'] ?? null,
                'noMejora'           => $data['noMejora'] ?? null,
                'descripcionMejora'  => $data['descripcionMejora'] ?? null,
                // En este ejemplo dejamos el campo "objetivo" vacío o lo podrías construir a partir de los objetivos ingresados.
                'objetivo'           => "",
                'areaImpacto'        => $data['areaImpacto'] ?? null,
                'personalBeneficiado'=> $data['personalBeneficiado'] ?? null,
                'situacionActual'    => $data['situacionActual'] ?? null,
                'indicadorExito'     => 0,
                'aprobacionNombre'   => $data['aprobacionNombre'] ?? null,
                'aprobacionPuesto'   => $data['aprobacionPuesto'] ?? null,
            ]);

            // Insertar cada objetivo (si se proporcionan)
            if (isset($data['objetivos'])) {
                foreach ($data['objetivos'] as $obj) {
                    Objetivo::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'descripcionObj'   => $obj['descripcion'] ?? null,
                    ]);
                }
            }

            // Insertar responsables
            if (isset($data['responsables'])) {
                foreach ($data['responsables'] as $resp) {
                    ResponsablePer::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'nombreRes'        => $resp['nombre'] ?? null,
                    ]);
                }
            }

            // Insertar indicadores de éxito
            if (isset($data['indicadoresExito'])) {
                foreach ($data['indicadoresExito'] as $ind) {
                    IndicadoresExito::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'nombreInd'        => $ind['nombre'] ?? null,
                    ]);
                }
            }

            // Insertar recursos
            if (isset($data['recursos'])) {
                foreach ($data['recursos'] as $rec) {
                    Recurso::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'descripcionRec'   => $rec['tiempoEstimado'] ?? null,
                        'recursosMatHum'   => $rec['recursosMatHum'] ?? null,
                        'costo'            => 0
                    ]);
                }
            }

            // Insertar plan de trabajo (actividades)
            if (isset($data['actividadesPM'])) {
                foreach ($data['actividadesPM'] as $act) {
                    ActividadesPM::create([
                        'idProyectoMejora' => $proyecto->idProyectoMejora,
                        'descripcionAct'   => $act['actividad'] ?? null,
                        'responsable'      => $act['responsable'] ?? null,
                        'fecha'            => $act['fecha'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Proyecto guardado correctamente',
                'data'    => $proyecto
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al guardar el proyecto',
                'error'   => $e->getMessage()
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
}
