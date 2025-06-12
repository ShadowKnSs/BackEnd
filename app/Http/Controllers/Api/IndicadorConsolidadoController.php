<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndicadorConsolidado;
use App\Models\ResultadoIndi;
use App\Models\EvaluaProveedores;
use App\Models\AnalisisDatos;
use App\Models\Registros;
use App\Models\NeceInter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndicadorConsolidadoController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!$request->filled('idRegistro')) {
                Log::error("❌ Parámetro idRegistro faltante o vacío.");
                return response()->json(['message' => 'El parámetro idRegistro es requerido.'], 400);
            }

            $idRegistro = $request->query('idRegistro');

            $registro = Registros::find($idRegistro);
            if (!$registro) {
                Log::error("❌ No se encontró Registro", ['idRegistro' => $idRegistro]);
                return response()->json(['message' => 'No se encontró un Registro asociado.'], 404);
            }

            Log::info("🔍 Registro encontrado", ['idRegistro' => $idRegistro, 'idProceso' => $registro->idProceso]);

            // Ahora filtramos indicadores SOLO por idRegistro
            $indicadores = IndicadorConsolidado::where('idRegistro', $idRegistro)->get();

            Log::info("✅ Indicadores filtrados", ['total' => $indicadores->count()]);

            return response()->json([
                'indicadores' => $indicadores,
                'idProceso' => $registro->idProceso
            ], 200);
            
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener indicadores", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al obtener los indicadores'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1️⃣ Validaciones básicas
            $data = $request->validate([
                'idRegistro' => 'required|integer|exists:registros,idRegistro',
                'origenIndicador' => 'required|string',
                'meta' => 'nullable|integer',
                'metodo' => 'nullable|string', // Solo se usa en retroalimentación
                'metaConfiable' => 'nullable|integer', // Solo si es evalua proveedores
                'metaCondicionado' => 'nullable|integer',
                'metaNoConfiable' => 'nullable|integer',
            ]);

            Log::info("📩 Datos recibidos para crear indicador", $data);

            // 2️⃣ Buscar el registro
            $registro = Registros::find($data['idRegistro']);
            if (!$registro) {
                Log::error("❌ No se encontró Registro", ['idRegistro' => $data['idRegistro']]);
                return response()->json(['message' => 'No se encontró el registro asociado.'], 404);
            }

            // 3️⃣ Obtener idProceso
            $data['idProceso'] = $registro->idProceso;

            // 4️⃣ Preparar datos según tipo de indicador
            if ($data['origenIndicador'] === 'Encuesta') {
                $data['nombreIndicador'] = "Encuesta de Satisfacción";
                $data['periodicidad'] = "Anual";
            } elseif ($data['origenIndicador'] === 'Retroalimentacion') {
                if (empty($data['metodo'])) {
                    Log::error("❌ Método faltante para retroalimentación");
                    return response()->json(['message' => 'El campo método es obligatorio para retroalimentación'], 400);
                }
                $data['nombreIndicador'] = "Retroalimentacion " . $data['metodo'];
                $data['periodicidad'] = "Anual";
            } elseif ($data['origenIndicador'] === 'EvaluaProveedores') {
                $data['nombreIndicador'] = "Evaluación de proveedores";
                $data['periodicidad'] = "Semestral";
            } else {
                Log::warning("⚠️ Tipo de indicador no reconocido", $data);
            }

            // 5️⃣ Crear el Indicador Consolidado
            $indicador = IndicadorConsolidado::create($data);
            Log::info("✅ Indicador Consolidado creado", ['idIndicador' => $indicador->idIndicador]);

            // 6️⃣ Crear el registro asociado en la tabla hija
            if ($data['origenIndicador'] === 'Encuesta') {
                DB::table('encuesta')->insert([
                    'idIndicador' => $indicador->idIndicador,
                    'malo' => 0,
                    'regular' => 0,
                    'bueno' => 0,
                    'excelente' => 0,
                    'noEncuestas' => 0,
                ]);
                Log::info("✅ Registro inicial creado en Encuesta", ['idIndicador' => $indicador->idIndicador]);
            } elseif ($data['origenIndicador'] === 'Retroalimentacion') {
                DB::table('retroalimentacion')->insert([
                    'idIndicador' => $indicador->idIndicador,
                    'metodo' => $data['metodo'],
                    'cantidadFelicitacion' => 0,
                    'cantidadSugerencia' => 0,
                    'cantidadQueja' => 0,
                    'total' => 0,
                ]);
                Log::info("✅ Registro inicial creado en Retroalimentacion", ['idIndicador' => $indicador->idIndicador]);
            } elseif ($data['origenIndicador'] === 'EvaluaProveedores') {
                EvaluaProveedores::create([
                    'idIndicador' => $indicador->idIndicador,
                    'metaConfiable' => $data['metaConfiable'] ?? 0,
                    'metaCondicionado' => $data['metaCondicionado'] ?? 0,
                    'metaNoConfiable' => $data['metaNoConfiable'] ?? 0,
                    'resultadoConfiableSem1' => 0,
                    'resultadoConfiableSem2' => 0,
                    'resultadoCondicionadoSem1' => 0,
                    'resultadoCondicionadoSem2' => 0,
                    'resultadoNoConfiableSem1' => 0,
                    'resultadoNoConfiableSem2' => 0,
                ]);
                Log::info("✅ Registro inicial creado en EvaluaProveedores", ['idIndicador' => $indicador->idIndicador]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Indicador creado exitosamente',
                'indicador' => $indicador
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error al crear indicador", ['error' => $e->getMessage()]);
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
                    DB::table('actividadcontrol')->where('idIndicador', $id)->delete();
                    break;
                case 'MapaProceso':
                    DB::table('indmapaproceso')->where('idIndicador', $id)->delete();
                    break;
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
        // 1. Buscar el registro del apartado Análisis de Datos
        $registro = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('Apartado', 'Análisis de Datos')
            ->first();

        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        // 2. Buscar análisis y su relación con NeceInter
        $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->first();
        $neceInter = null;

        if ($analisis) {
            $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                ->where('seccion', 'Conformidad')
                ->first();
        }

        $interpretacion = $neceInter->Interpretacion ?? 'No disponible';
        $necesidad = $neceInter->Necesidad ?? 'No disponible';

        // 3. Obtener indicadores ActividadControl
        $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
            ->where('origenIndicador', 'ActividadControl')
            ->get();

        $resultado = $indicadores->map(function ($indicador) use ($interpretacion, $necesidad) {
            $act = DB::table('actividadcontrol')->where('idIndicador', $indicador->idIndicador)->first();
            $res = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();

            return [
                'idIndicador' => $indicador->idIndicador,
                'nombreIndicador' => $indicador->nombreIndicador,
                'meta' => $indicador->meta,
                'resultadoSemestral1' => $res->resultadoSemestral1 ?? 0,
                'resultadoSemestral2' => $res->resultadoSemestral2 ?? 0,
                'procedimiento' => $act->procedimiento ?? null,
                'interpretacion' => $interpretacion,
                'necesidad' => $necesidad,
            ];
        });

        return response()->json($resultado);
    }



    public function obtenerIndGesRiesgos(Request $request)
    {
        try {
            $idProceso = $request->input('idProceso');
            $anio = $request->input('año');

            if (!$idProceso || !$anio) {
                return response()->json(['message' => 'Parámetros idProceso y año son requeridos.'], 400);
            }

            // 1. Buscar el idRegistro del apartado "Gestion de Riesgo"
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('Apartado', 'Gestion de Riesgo')
                ->first();

            if (!$registro) {
                return response()->json(['message' => 'Registro no encontrado.'], 404);
            }

            // 2. Obtener indicadores vinculados a ese registro
            $indicadores = IndicadorConsolidado::where('idRegistro', $registro->idRegistro)->get();

            return response()->json([
                'idRegistro' => $registro->idRegistro,
                'indicadores' => $indicadores,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error en porProcesoYAnio()", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al obtener indicadores.'], 500);
        }
    }

    public function indexConDetalles(Request $request)
    {
        $idRegistro = $request->query('idRegistro');
        $idProceso = $request->query('idProceso');
        $tipo = $request->query('tipo'); // para detectar estructura

        $query = IndicadorConsolidado::query()
            ->with([
                'encuesta:idIndicador,malo,regular,excelente,bueno,noEncuestas',
                'retroalimentacion:idIndicador,cantidadFelicitacion,cantidadSugerencia,cantidadQueja',
                'evaluaProveedores:idIndicador,resultadoConfiableSem1,resultadoConfiableSem2,resultadoCondicionadoSem1,resultadoCondicionadoSem2,resultadoNoConfiableSem1,resultadoNoConfiableSem2',
                'resultadoIndi:idIndicador,resultadoSemestral1,resultadoSemestral2,resultadoAnual',
                'indicadorMapaProceso:idIndicador,descripcion,formula,periodoMed,responsable'
            ])
            ->select('idIndicador', 'nombreIndicador', 'origenIndicador', 'meta', 'periodicidad');

        if ($idRegistro) {
            $query->where('idRegistro', $idRegistro)
                ->whereNotIn('origenIndicador', ['ActividadControl', 'MapaProceso']);
        } elseif ($idProceso && $tipo === 'estructura') {
            $query->where('idProceso', $idProceso)
                ->whereIn('origenIndicador', ['ActividadControl', 'MapaProceso']);
        } else {
            return response()->json(['message' => 'Parámetros inválidos.'], 400);
        }

        $indicadores = $query->get()->map(function ($ind) {
            $flat = $ind->toArray();
            if (isset($flat['resultado_indi'])) {
                $flat = array_merge($flat, $flat['resultado_indi']);
                unset($flat['resultado_indi']);
            }

            return $flat;
        });

        return response()->json(['indicadores' => $indicadores]);
    }




}

