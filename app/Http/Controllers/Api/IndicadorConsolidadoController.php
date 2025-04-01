<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndicadorConsolidado;
use App\Models\ResultadoIndi;
use App\Models\EvaluaProveedores;
use App\Models\AnalisisDatos;
use App\Models\Registros;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndicadorConsolidadoController extends Controller
{
    /**
     * Crea un nuevo indicador y su registro inicial en la tabla correspondiente según el tipo.
     *
     * Se espera que el request incluya, al menos, los siguientes campos:
     * - idRegistro (obtenido desde la ruta o enviado en el payload)
     * - meta (valor numérico, proporcionado por el usuario)
     * - idProceso (según la lógica de la aplicación)
     * - Para Retroalimentacion: se espera además el campo "metodo"
     *
     * La lógica es:
     * 1. Según el tipo (origenIndicador) se fijan ciertos valores:
     *    - Encuesta:
     *       nombreIndicador = "Encuesta de Satisfacción"
     *       origenIndicador = "Encuesta"
     *       periodicidad = "Anual"
     *    - Retroalimentacion:
     *       nombreIndicador = "Retroalimentacion <metodo>"
     *       origenIndicador = "Retroalimentacion"
     *       periodicidad = "Anual"
     *    - EvaluaProveedores:
     *       nombreIndicador = "Evaluación de proveedores"
     *       origenIndicador = "EvaluaProveedores"
     *       periodicidad = "Semestral"
     *
     * 2. Se crea el registro en IndicadorConsolidado.
     * 3. Con el idIndicador generado, se inserta un registro en la tabla correspondiente:
     *    - Para Encuesta: se inicializan los campos (malo, regular, bueno, excelente, noEncuestas) en 0.
     *    - Para Retroalimentacion: se inserta el método y se inicializan las cantidades en 0, junto con total = 0.
     *    - Para EvaluaProveedores: se inicializan (confiable, condicionado, noConfiable, resultadoSemestral1, resultadoSemestral2) en 0.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // 📌 Validar que idRegistro está presente
            if (!$request->has('idRegistro')) {
                Log::error("❌ Parámetro idRegistro faltante.");
                return response()->json(['message' => 'El parámetro idRegistro es requerido.'], 400);
            }

            $idRegistro = $request->query('idRegistro');

            // 📌 Buscar el idProceso en la tabla Registros
            $registro = DB::table('Registros')->where('idRegistro', $idRegistro)->first();
            if (!$registro) {
                Log::error("❌ No se encontró registro para idRegistro", ['idRegistro' => $idRegistro]);
                return response()->json(['message' => 'No se encontró un registro con ese idRegistro.'], 404);
            }

            $idProceso = $registro->idProceso;
            Log::info("📌 idProceso obtenido:", ['idRegistro' => $idRegistro, 'idProceso' => $idProceso]);

            // 📌 Filtrar indicadores por idRegistro y idProceso
            $indicadores = IndicadorConsolidado::where('idRegistro', $idRegistro)
                ->orWhere('idProceso', $idProceso)
                ->get();

            Log::info("📌 Indicadores filtrados:", ['total' => count($indicadores)]);

            return response()->json(['indicadores' => $indicadores], 200);

        } catch (\Exception $e) {
            Log::error("❌ Error al obtener los indicadores:", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al obtener los indicadores'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Capturar los datos enviados
            $data = $request->all();
            Log::info("Datos recibidos para crear indicador", $data);

            // Validar que idRegistro esté presente
            if (empty($data['idRegistro'])) {
                Log::error("idRegistro no proporcionado en la solicitud", $data);
                return response()->json(['message' => 'El idRegistro es requerido.'], 400);
            }

            // Obtener idProceso desde la tabla Registros
            $registro = DB::table('Registros')->where('idRegistro', $data['idRegistro'])->first();
            if (!$registro) {
                Log::error("No se encontró registro en la tabla Registros para idRegistro: " . $data['idRegistro']);
                return response()->json(['message' => 'No se encontró el registro.'], 404);
            }
            $data['idProceso'] = $registro->idProceso;
            Log::info("Registro encontrado en Registros", ['idRegistro' => $data['idRegistro'], 'idProceso' => $registro->idProceso]);

            // Validar que meta esté presente solo si el tipo no es EvaluaProveedores
            if ($data['origenIndicador'] !== 'EvaluaProveedores' && empty($data['meta'])) {
                Log::error("El campo meta es requerido", $data);
                return response()->json(['message' => 'El campo meta es requerido.'], 400);
            }

            // Ajustar datos según el tipo de indicador
            if ($data['origenIndicador'] === 'Encuesta') {
                $data['nombreIndicador'] = "Encuesta de Satisfacción";
                $data['periodicidad'] = "Anual";
                Log::info("Preparando indicador de tipo Encuesta", $data);
            } elseif ($data['origenIndicador'] === 'Retroalimentacion') {
                if (empty($data['metodo'])) {
                    Log::error("Método no proporcionado para indicador de Retroalimentación", $data);
                    return response()->json(['message' => 'El campo método es obligatorio.'], 400);
                }
                $data['nombreIndicador'] = "Retroalimentacion " . $data['metodo'];
                $data['periodicidad'] = "Anual";
                Log::info("Preparando indicador de tipo Retroalimentacion", $data);
            } elseif ($data['origenIndicador'] === 'EvaluaProveedores') {
                $data['nombreIndicador'] = "Evaluación de proveedores";
                $data['periodicidad'] = "Semestral";
                Log::info("Preparando indicador de tipo EvaluaProveedores", $data);
            } else {
                Log::warning("Tipo de indicador no reconocido", $data);
            }

            // Crear el indicador en la tabla IndicadoresConsolidados
            $indicador = IndicadorConsolidado::create($data);
            $idIndicador = $indicador->idIndicador;
            Log::info("IndicadorConsolidado creado", ['idIndicador' => $idIndicador, 'datos' => $indicador->toArray()]);

            // Inserción en la tabla hija según el origen
            if ($data['origenIndicador'] === 'Encuesta') {
                DB::table('encuesta')->insert([
                    'idIndicador' => $idIndicador,
                    'malo' => 0,
                    'regular' => 0,
                    'bueno' => 0,
                    'excelente' => 0,
                    'noEncuestas' => 0,
                ]);
                Log::info("Registro inicial insertado en la tabla encuesta", ['idIndicador' => $idIndicador]);

            } elseif ($data['origenIndicador'] === 'Retroalimentacion') {
                DB::table('retroalimentacion')->insert([
                    'idIndicador' => $idIndicador,
                    'metodo' => $data['metodo'],
                    'cantidadFelicitacion' => 0,
                    'cantidadSugerencia' => 0,
                    'cantidadQueja' => 0,
                    'total' => 0,
                ]);
                Log::info("Registro inicial insertado en la tabla retroalimentacion", ['idIndicador' => $idIndicador]);

            } elseif ($data['origenIndicador'] === 'EvaluaProveedores') {
                // Se insertan los registros semestrales en cada atributo
                EvaluaProveedores::create([
                    'idIndicador' => $idIndicador,
                    'metaConfiable' => $data['metaConfiable'],
                    'metaCondicionado' => $data['metaCondicionado'],
                    'metaNoConfiable' => $data['metaNoConfiable'],
                    'resultadoConfiableSem1' => 0,
                    'resultadoConfiableSem2' => 0,
                    'resultadoCondicionadoSem1' => 0,
                    'resultadoCondicionadoSem2' => 0,
                    'resultadoNoConfiableSem1' => 0,
                    'resultadoNoConfiableSem2' => 0,
                ]);
                Log::info("Registro inicial insertado en la tabla evaluaProveedores", ['idIndicador' => $idIndicador]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Indicador creado exitosamente',
                'indicador' => $indicador
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear indicador", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al crear el indicador',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Muestra un indicador consolidado específico por su ID.
     *
     * @param int $id (idIndicador)
     */
    public function show($id)
    {
        try {
            $indicador = IndicadorConsolidado::findOrFail($id);
            Log::info("Indicador obtenido", ['idIndicador' => $id]);
            return response()->json(['indicador' => $indicador], 200);
        } catch (\Exception $e) {
            Log::error("Error al obtener indicador", ['idIndicador' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Indicador no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualiza los datos base de un indicador consolidado.
     *
     * @param Request $request
     * @param int $id (idIndicador)
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $indicador = IndicadorConsolidado::findOrFail($id);
            $input = $request->all();
            Log::info("Datos para actualizar indicador", ['idIndicador' => $id, 'input' => $input]);

            // Actualizar solo los campos permitidos
            $fieldsToUpdate = [
                'nombreIndicador',
                'descripcionIndicador',
                'origenIndicador',
                'periodicidad',
                'meta'
            ];
            $indicador->update($request->only($fieldsToUpdate));
            DB::commit();
            Log::info("Indicador actualizado correctamente", ['idIndicador' => $id]);
            return response()->json([
                'message' => 'Indicador actualizado',
                'indicador' => $indicador
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar indicador", ['idIndicador' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al actualizar el indicador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un indicador consolidado y sus registros asociados en las tablas hijas.
     *
     * Se asume que en la tabla "analisisdatos" se relaciona el indicador mediante el campo "idIndicador".
     *
     * @param int $id (idIndicador)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $indicador = IndicadorConsolidado::findOrFail($id);
            Log::info("Iniciando eliminación del indicador", ['idIndicador' => $id]);

            // 🔹 Eliminar registros en la tabla correspondiente según el origenIndicador
            switch ($indicador->origenIndicador) {
                case 'Encuesta':
                    DB::table('encuesta')->where('idIndicador', $id)->delete();
                    Log::info("Registro en encuesta eliminado", ['idIndicador' => $id]);
                    break;
                case 'Retroalimentacion':
                    DB::table('retroalimentacion')->where('idIndicador', $id)->delete();
                    Log::info("Registro en retroalimentacion eliminado", ['idIndicador' => $id]);
                    break;
                case 'EvaluaProveedores':
                    DB::table('evaluaProveedores')->where('idIndicador', $id)->delete();
                    Log::info("Registro en evaluaProveedores eliminado", ['idIndicador' => $id]);
                    break;
                case 'ActividadControl':
                case 'MapaProceso':
                case 'GestionRiesgo':
                    DB::table('ResultadoIndi')->where('idIndicador', $id)->delete();
                    Log::info("Registro en resultadoIndi eliminado", ['idIndicador' => $id]);
                    break;
                default:
                    Log::info("No hay registro hijo a eliminar para el origen: " . $indicador->origenIndicador);
                    break;
            }

            // 🔹 Eliminar el indicador consolidado
            $indicador->delete();
            DB::commit();
            Log::info("Indicador y registros asociados eliminados exitosamente", ['idIndicador' => $id]);

            return response()->json([
                'message' => 'Indicador y sus registros asociados eliminados correctamente.',
                'indicador' => $indicador
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar indicador", ['idIndicador' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al eliminar el indicador.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function actividadControl($idProceso, $anio)
    {
        Log::info("🔍 Buscando idRegistro para proceso {$idProceso} y año {$anio}");

        // 1. Buscar el registro relacionado al análisis de datos
        $registro = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('Apartado', 'Análisis de Datos')
            ->first();

        if (!$registro) {
            return response()->json(['error' => 'Registro de análisis de datos no encontrado'], 404);
        }

        Log::info("✅ idRegistro encontrado: {$registro->idRegistro}");

        // 2. Obtener las interpretaciones y necesidades de la sección Conformidad
        $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)
            ->where('seccion', 'Conformidad')
            ->first();

        Log::info("📊 Análisis agrupado por idIndicador:", $analisis->toArray());



        // 3. Obtener indicadores de tipo ActividadControl
        $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
            ->where('origenIndicador', 'ActividadControl')
            ->get();

        // 4. Armar respuesta
        $resultado = $indicadores->map(function ($indicador) use ($analisis) {
            $resultados = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();

            return [
                'idIndicador' => $indicador->idIndicador,
                'nombreIndicador' => $indicador->nombreIndicador,
                'meta' => $indicador->meta,
                'resultadoSemestral1' => $resultados->resultadoSemestral1 ?? null,
                'resultadoSemestral2' => $resultados->resultadoSemestral2 ?? null,
                'interpretacion' => $analisis->interpretacion ?? null,
            'necesidad' => $analisis->necesidad ?? null,
            ];
        });

        return response()->json($resultado);
    }


    public function obtenerIndicadoresConsolidados($idProceso)
{
    try {
        // Suponiendo que tienes un modelo IndicadorConsolidado que representa la tabla de indicadores consolidados
        $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)->get();

        return response()->json(['indicadores' => $indicadores], 200);
    } catch (\Exception $e) {
        \Log::error('Error al obtener indicadores consolidados: ' . $e->getMessage());
        return response()->json(['error' => 'Error al obtener indicadores consolidados'], 500);
    }
}
}

