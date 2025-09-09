<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Registros;
use App\Models\Proceso;
use Carbon\Carbon;
use App\Models\IndicadorConsolidado;
use App\Models\AnalisisDatos;
use App\Models\Encuesta;
use App\Models\Retroalimentacion;
use App\Models\EntidadDependencia;
use App\Models\EvaluaProveedores;
use App\Models\ActividadMejora;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class RegistrosController extends Controller
{

    // Crear un nuevo registro
    public function store(Request $request)
    {
        $request->validate([
            'idProceso' => 'required|integer|exists:proceso,idProceso',
            'año' => 'required|integer',
            'Apartado' => 'required|string',
        ]);

        // Verificar si ya existe un registro con el mismo idProceso, año y apartado
        $registroExistente = Registros::where('idProceso', $request->idProceso)
            ->where('año', $request->año)
            ->where('Apartado', $request->Apartado)
            ->first();

        if ($registroExistente) {
            return response()->json(['message' => 'La carpeta ya existe'], 409);
        }

        // Crear el nuevo registro
        $registro = Registros::create($request->all());


        // Solo para "Acciones de Mejora"
        if ($request->Apartado === "Acciones de Mejora") {
            try {
                $idRegistro = $registro->idRegistro;

                // Verifica si ya existe la actividad de mejora (por si acaso)
                $existeActividad = ActividadMejora::where('idRegistro', $idRegistro)->exists();

                if (!$existeActividad) {
                    ActividadMejora::create([
                        'idRegistro' => $idRegistro
                    ]);

                    Log::info("✅ ActividadMejora creada automáticamente para el registro", ['idRegistro' => $idRegistro]);
                }
            } catch (\Exception $e) {
                Log::error("❌ Error al crear ActividadMejora automática", ['error' => $e->getMessage()]);
            }
        }

        //Solo para "Análisis de Datos"
        if ($request->Apartado === "Análisis de Datos") {
            try {
                $idRegistro = $registro->idRegistro;

                // 🔹 Crear indicador de Encuesta si no existe en este registro
                $existeEncuesta = IndicadorConsolidado::where('idRegistro', $idRegistro)
                    ->where('origenIndicador', 'Encuesta')
                    ->exists();

                if (!$existeEncuesta) {
                    $indicadorEncuesta = IndicadorConsolidado::create([
                        'idRegistro' => $idRegistro,
                        'idProceso' => $request->idProceso,
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

                    Log::info("✅ Indicador 'Encuesta de Satisfacción' creado automáticamente.", ['idRegistro' => $idRegistro]);
                }

                // 🔹 Crear indicadores de Retroalimentación (Encuesta, Buzon Virtual, Buzon Fisico)
                $metodos = ['Encuesta', 'Buzon Virtual', 'Buzon Fisico'];

                foreach ($metodos as $metodo) {
                    $existeRetro = IndicadorConsolidado::where('idRegistro', $idRegistro)
                        ->where('origenIndicador', 'Retroalimentacion')
                        ->where('nombreIndicador', 'like', "%$metodo%")
                        ->exists();

                    if (!$existeRetro) {
                        $indicadorRetro = IndicadorConsolidado::create([
                            'idRegistro' => $idRegistro,
                            'idProceso' => $request->idProceso,
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
                }

                // 🔹 Crear indicador EvaluaProveedores
                $indicadorEvalua = IndicadorConsolidado::create([
                    'idRegistro' => $idRegistro,
                    'idProceso' => $request->idProceso,
                    'nombreIndicador' => "Evaluación de Proveedores",
                    'origenIndicador' => "EvaluaProveedores",
                    'periodicidad' => "Semestral",
                    'meta' => 100,
                ]);

                // Inicializar evaluaProveedores
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

                Log::info("✅ Indicadores automáticos creados correctamente en Análisis de Datos");

            } catch (\Exception $e) {
                Log::error("❌ Error al crear indicadores automáticos para Análisis de Datos", ['error' => $e->getMessage()]);
            }
        }

        return response()->json($registro, 201);
    }

    // Mostrar un solo registro
    public function show($id)
    {
        $registro = Registros::findOrFail($id);
        return response()->json($registro);
    }

    // Actualizar un registro
    public function update(Request $request, $id)
    {
        $registro = Registros::findOrFail($id);
        $registro->update($request->all());
        return response()->json($registro);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $registro = Registros::findOrFail($id);
        $registro->delete();
        return response()->json(['message' => 'Registro eliminado']);
    }
    public function obtenerRegistrosPorProcesoYApartado(Request $request)
    {
        $request->validate([
            'idProceso' => 'required|integer',
            'Apartado' => 'required|string',
        ]);

        $registros = Registros::where('idProceso', $request->idProceso)
            ->where('Apartado', $request->Apartado)
            ->get();

        return response()->json($registros);
    }

    public function obtenerAnios($idProceso)
    {
        $proceso = Proceso::find($idProceso);
        $processYear = null;
        if ($proceso) {
            // Se asume que el año se obtiene a partir del campo created_at
            $processYear = Carbon::parse($proceso->created_at)->year;
        }

        // Obtener los años distintos de los registros asociados al idProceso
        $years = Registros::where('idProceso', $idProceso)
            ->distinct()
            ->pluck('año')
            ->toArray();

        // Agregar el año de creación del proceso si no se encuentra ya en el listado
        if ($processYear && !in_array($processYear, $years)) {
            $years[] = $processYear;
        }

        // Ordenar el arreglo de años (por ejemplo, en orden ascendente)
        sort($years);

        Log::info("Años obtenidos: " . implode(', ', $years));

        return response()->json($years);
    }

    public function obtenerIdRegistro(Request $request)
    {
        Log::info("🔍 Entrando a obtenerIdRegistro"); // ✅ este debería salir

        $idProceso = $request->query('idProceso');
        $anio = $request->query('año');
        $apartado = $request->query('apartado', 'Análisis de Datos');


        $registro = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('Apartado', $apartado)
            ->first();

        if (!$registro) {
            Log::warning("⚠️ Registro no encontrado", compact('idProceso', 'anio', 'apartado'));
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
        Log::info("✅ Registro encontrado", ['idRegistro' => $registro->idRegistro]);

        return response()->json(['idRegistro' => $registro->idRegistro]);
    }



    public function buscarProceso($idRegistro)
    {
        $registro = Registros::select('idProceso')->find($idRegistro);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['idProceso' => $registro->idProceso], 200);
    }

    public function updateCarpeta(Request $request, $id)
{
    $registro = Registros::findOrFail($id);

    $validated = $request->validate([
        'año' => [
            'required',
            'integer',
            Rule::unique('Registros')->where(function ($query) use ($registro) {
                return $query
                    ->where('idProceso', $registro->idProceso)
                    ->where('Apartado', $registro->Apartado);
            })->ignore($registro->idRegistro, 'idRegistro')
        ]
    ]);

    $registro->año = $validated['año'];
    $registro->save();

    return response()->json($registro);
}
   
}
