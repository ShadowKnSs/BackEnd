<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\GestionRiesgos;
use App\Models\Registros;
use App\Models\ActividadMejora;
use App\Models\IndicadorConsolidado;
use App\Models\EvaluaProveedores;
use App\Models\PlanTrabajo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


class ProcessController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            //Validar que no exista proceso con el mismo nombre en la misma entidad
            $existe = Proceso::where('idEntidad', $request->idEntidad)
                ->where('nombreProceso', $request->nombreProceso)
                ->first();

            if ($existe) {
                return response()->json([
                    'error' => 'Ya existe un proceso con este nombre en la misma entidad.'
                ], 422); // 422 Unprocessable Entity
            }
            // Crear proceso
            $proceso = Proceso::create($request->all());

            $año = now()->year;
            $apartados = [
                "Auditoría",
                "Seguimiento",
                "Acciones de Mejora",
                "Gestión de Riesgo",
                "Análisis de Datos"
            ];

            foreach ($apartados as $apartado) {
                $registro = Registros::create([
                    'idProceso' => $proceso->idProceso,
                    'año' => $año,
                    'Apartado' => $apartado
                ]);

                if ($apartado === "Gestión de Riesgo") {
                    GestionRiesgos::firstOrCreate(
                        ['idRegistro' => $registro->idRegistro],
                        ['elaboro' => null, 'fechaelaboracion' => null]
                    );
                    Log::info("gestionriesgos creado automáticamente", [
                        'idRegistro' => $registro->idRegistro
                    ]);
                }
                if ($apartado === "Acciones de Mejora") {
                    $actividadMejora = ActividadMejora::firstOrCreate([
                        'idRegistro' => $registro->idRegistro
                    ]);

                    PlanTrabajo::firstOrCreate(
                        ['idActividadMejora' => $actividadMejora->idActividadMejora],
                        [
                            'fechaElaboracion' => now(),
                            'objetivo' => 'Plan de Trabajo para ' . $proceso->nombreProceso,
                            'revisadoPor' => null,
                            'responsable' => null,
                            'estado' => 'En proceso',
                            'fuente' => 'Gestión de Riesgos',
                            'entregable' => 'Cumplimiento de actividades'
                        ]
                    );

                    Log::info("ActividadMejora y PlanTrabajo creados automáticamente", [
                        'idRegistro' => $registro->idRegistro,
                        'idActividadMejora' => $actividadMejora->idActividadMejora
                    ]);
                }

                if ($apartado === "Análisis de Datos") {
                    // Indicador Encuesta
                    $indicadorEncuesta = IndicadorConsolidado::create([
                        'idRegistro' => $registro->idRegistro,
                        'idProceso' => $proceso->idProceso,
                        'nombreIndicador' => "Encuesta de Satisfacción",
                        'origenIndicador' => "Encuesta",
                        'periodicidad' => "Anual",
                        'meta' => 100,
                    ]);

                    DB::table('encuesta')->insert([
                        'idIndicador' => $indicadorEncuesta->idIndicador,
                        'malo' => null,
                        'regular' => null,
                        'bueno' => null,
                        'excelente' => null,
                        'noEncuestas' => null,
                    ]);

                    // Retroalimentación
                    $metodos = ['Encuesta', 'Buzon Virtual', 'Buzon Fisico'];
                    foreach ($metodos as $metodo) {
                        $indicadorRetro = IndicadorConsolidado::create([
                            'idRegistro' => $registro->idRegistro,
                            'idProceso' => $proceso->idProceso,
                            'nombreIndicador' => "Retroalimentacion $metodo",
                            'origenIndicador' => "Retroalimentacion",
                            'periodicidad' => "Anual",
                            'meta' => 100,
                        ]);

                        DB::table('retroalimentacion')->insert([
                            'idIndicador' => $indicadorRetro->idIndicador,
                            'metodo' => $metodo,
                            'cantidadFelicitacion' => null,
                            'cantidadSugerencia' => null,
                            'cantidadQueja' => null,
                            'total' => null,
                        ]);
                    }

                    // Evaluación de Proveedores
                    $indicadorEvalua = IndicadorConsolidado::create([
                        'idRegistro' => $registro->idRegistro,
                        'idProceso' => $proceso->idProceso,
                        'nombreIndicador' => "Evaluación de Proveedores",
                        'origenIndicador' => "EvaluaProveedores",
                        'periodicidad' => "Semestral",
                        'meta' => 100,
                    ]);

                    EvaluaProveedores::create([
                        'idIndicador' => $indicadorEvalua->idIndicador,
                        'metaConfiable' => 90,
                        'metaCondicionado' => 70,
                        'metaNoConfiable' => 50,
                        'resultadoConfiableSem1' => null,
                        'resultadoConfiableSem2' => null,
                        'resultadoCondicionadoSem1' => null,
                        'resultadoCondicionadoSem2' => null,
                        'resultadoNoConfiableSem1' => null,
                        'resultadoNoConfiableSem2' => null,
                    ]);

                    Log::info("Indicadores automáticos creados correctamente en Análisis de Datos", [
                        'idRegistro' => $registro->idRegistro
                    ]);

                    // Crear el registro de análisis de datos
                    $analisisDatosId = DB::table('analisisdatos')->insertGetId([
                        'idRegistro' => $registro->idRegistro,
                        'periodoEvaluacion' => null,
                    ]);

                    Log::info(" Registro de análisis de datos creado automáticamente", [
                        'idRegistro' => $registro->idRegistro
                    ]);

                    // Crear también la sección 'Conformidad' en NeceInter
                    DB::table('NeceInter')->insert([
                        'idAnalisisDatos' => $analisisDatosId,
                        'seccion' => 'Conformidad',
                        'interpretacion' => '',
                        'necesidad' => '',
                    ]);

                    Log::info("NeceInter con sección Conformidad creado automáticamente", [
                        'idAnalisisDatos' => $analisisDatosId
                    ]);

                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Proceso y registros creados exitosamente',
                'proceso' => $proceso
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear proceso o registros', [
                'error' => $e->getMessage(),
                'datos' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error al crear el proceso y registros',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /*public function index()
    {
        $procesos = Proceso::all();
        return response()->json(['procesos' => $procesos], 200);
    }*/
    public function index()
    {
        $procesos = Proceso::all(); // devuelve todos los procesos
        return response()->json($procesos);
    }

    /*public function show($id)
    {
        $proceso = Proceso::findOrFail($id);
        return response()->json(['proceso' => $proceso], 200);
    }*/
    //Para poder consumir tambien el macroproceso, entidad y usuario
    public function show($id)
    {
        $proceso = Proceso::with(['macroproceso', 'entidad', 'usuario'])->find($id);

        if (!$proceso) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }

        return response()->json(['proceso' => $proceso], 200);
    }

    public function update(Request $request, $id)
    {
        $proceso = Proceso::findOrFail($id);
        $proceso->update($request->all());
        return response()->json(['proceso' => $proceso], 200);
    }

    /* public function destroy($id)
     {
         $proceso = Proceso::findOrFail($id);
         $proceso->delete();
         return response()->json(['proceso' => $proceso], 200);
     }*/

    public function destroy($id)
    {
        $proceso = Proceso::findOrFail($id);

        // Cambiar el estado a "Inactivo"
        $proceso->estado = 'Inactivo';
        $proceso->save();

        return response()->json(['proceso' => $proceso], 200);
    }

    // Obtener solo los nombres de los procesos
    public function getNombres()
    {
        $nombres = Proceso::pluck('nombreProceso');
        return response()->json(['procesos' => $nombres], 200);
    }


    /*public function obtenerProcesosPorEntidad($idEntidad)
    {
        // Obtener todos los procesos de la entidad específica
        $procesos = Proceso::where('idEntidad', $idEntidad )->get();

        if ($procesos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron procesos para esta entidad'], 404);
        }

        return response()->json($procesos);
    }*/
    public function obtenerProcesosPorEntidad($idEntidad)
    {
        // Obtener solo los procesos activos de la entidad específica
        $procesos = Proceso::where('idEntidad', $idEntidad)
            ->where('estado', 'Activo')
            ->get();

        if ($procesos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron procesos activos para esta entidad'], 404);
        }

        return response()->json($procesos);
    }


    public function obtenerProcesoPorUsuario($idUsuario)
    {
        $proceso = Proceso::where('idUsuario', $idUsuario)->first();
        return response()->json($proceso);
    }


    public function getInfoPorProceso($idProceso)
    {
        $proceso = Proceso::with('entidad')->find($idProceso);

        if (!$proceso || !$proceso->entidad) {
            return response()->json(['error' => 'Datos incompletos'], 404);
        }

        return response()->json([
            'proceso' => $proceso->nombreProceso,
            'entidad' => $proceso->entidad->nombreEntidad,
        ]);
    }


    public function procesosConEntidad()
    {
        // 1. Usar caché para datos que no cambian frecuentemente
        $cacheKey = 'procesos_con_entidad_active';
        $cacheTime = 3600; // 1 hora

        $procesos = Cache::remember($cacheKey, $cacheTime, function () {
            return DB::table('proceso as p')
                ->join('entidaddependencia as e', 'p.idEntidad', '=', 'e.idEntidadDependencia')
                ->select(
                    'p.idProceso',
                    'e.nombreEntidad',
                    'p.nombreProceso',
                    DB::raw("CONCAT(e.nombreEntidad, ' - ', p.nombreProceso) AS nombreCompleto")
                )
                ->where('p.estado', 'Activo')
                // 2. Asegurar índices
                ->whereRaw('p.idEntidad = e.idEntidadDependencia')
                // 3. Orden consistente
                ->orderBy('e.nombreEntidad')
                ->orderBy('p.nombreProceso')
                ->get();
        });

        return response()->json(['procesos' => $procesos]);
    }


    public function obtenerProcesoConLider($idProceso)
    {
        $proceso = Proceso::with(['usuario', 'entidad'])->find($idProceso);

        if (!$proceso) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }

        $liderProceso = $proceso->usuario
            ? $proceso->usuario->nombre . ' ' . $proceso->usuario->apellidoPat . ' ' . $proceso->usuario->apellidoMat
            : null;

        return response()->json([
            'proceso' => $proceso->nombreProceso,
            'entidad' => $proceso->entidad->nombreEntidad ?? null,
            'liderProceso' => $liderProceso
        ]);
    }

}

