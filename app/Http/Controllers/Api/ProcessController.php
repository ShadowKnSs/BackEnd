<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\Registros;
use App\Models\ActividadMejora;
use App\Models\IndicadorConsolidado;
use App\Models\EvaluaProveedores;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class ProcessController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
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

                if ($apartado === "Acciones de Mejora") {
                    ActividadMejora::firstOrCreate([
                        'idRegistro' => $registro->idRegistro
                    ]);
                    Log::info("✅ ActividadMejora creada automáticamente al crear proceso", [
                        'idRegistro' => $registro->idRegistro
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
                        'malo' => 0,
                        'regular' => 0,
                        'bueno' => 0,
                        'excelente' => 0,
                        'noEncuestas' => 0,
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
                            'cantidadFelicitacion' => 0,
                            'cantidadSugerencia' => 0,
                            'cantidadQueja' => 0,
                            'total' => 0,
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
                        'resultadoConfiableSem1' => 0,
                        'resultadoConfiableSem2' => 0,
                        'resultadoCondicionadoSem1' => 0,
                        'resultadoCondicionadoSem2' => 0,
                        'resultadoNoConfiableSem1' => 0,
                        'resultadoNoConfiableSem2' => 0,
                    ]);

                    Log::info("✅ Indicadores automáticos creados correctamente en Análisis de Datos", [
                        'idRegistro' => $registro->idRegistro
                    ]);

                    // Crear el registro de análisis de datos
                    $analisisDatosId = DB::table('analisisdatos')->insertGetId([
                        'idRegistro' => $registro->idRegistro,
                        'periodoEvaluacion' => null,
                    ]);

                    Log::info("✅ Registro de análisis de datos creado automáticamente", [
                        'idRegistro' => $registro->idRegistro
                    ]);

                    // Crear también la sección 'Conformidad' en NeceInter
                    DB::table('neceinter')->insert([
                        'idAnalisisDatos' => $analisisDatosId,
                        'seccion' => 'Conformidad',
                        'interpretacion' => '',
                        'necesidad' => '',
                    ]);

                    Log::info("✅ NeceInter con sección Conformidad creado automáticamente", [
                        'idAnalisisDatos' => $analisisDatosId
                    ]);

                }
            }

            DB::commit(); // ✅ AHORA SÍ, AL FINAL DE TODO

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



    public function index()
    {
        $procesos = Proceso::all();
        return response()->json(['procesos' => $procesos], 200);
    }

    public function show($id)
    {
        $proceso = Proceso::findOrFail($id);
        return response()->json(['proceso' => $proceso], 200);
    }

    public function update(Request $request, $id)
    {
        $proceso = Proceso::findOrFail($id);
        //Me falta la validacion
        $proceso->update($request->all());
        return response()->json(['proceso' => $proceso], 200);
    }

    public function destroy($id)
    {
        $proceso = Proceso::findOrFail($id);
        $proceso->delete();
        return response()->json(['proceso' => $proceso], 200);
    }

    // Obtener solo los nombres de los procesos
    public function getNombres()
    {
        $nombres = Proceso::pluck('nombreProceso');
        return response()->json(['procesos' => $nombres], 200);
    }


    public function obtenerProcesosPorEntidad($idEntidad)
    {
        // Obtener todos los procesos de la entidad específica
        $procesos = Proceso::where('idEntidad', $idEntidad)->get();

        if ($procesos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron procesos para esta entidad'], 404);
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
        $proceso = Proceso::find($idProceso);

        if (!$proceso) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }

        $entidad = EntidadDependencia::find($proceso->idEntidad);

        if (!$entidad) {
            return response()->json(['error' => 'Entidad no encontrada'], 404);
        }

        return response()->json([
            'proceso' => $proceso->nombreProceso,
            'entidad' => $entidad->nombreEntidad,
        ]);
    }

    public function procesosConEntidad()
    {
        $procesos = DB::table('proceso')
            ->join('entidaddependencia', 'proceso.idEntidad', '=', 'entidaddependencia.idEntidadDependencia')
            ->select(
                'proceso.idProceso',
                DB::raw("CONCAT(entidaddependencia.nombreEntidad, ' - ', proceso.nombreProceso) AS nombreCompleto")
            )
            ->where('proceso.estado', 'Activo')
            ->get();

        return response()->json(['procesos' => $procesos]);
    }


}

