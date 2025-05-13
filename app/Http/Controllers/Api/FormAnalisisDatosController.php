<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\formAnalisisDatos;
use App\Models\AnalisisDatos;
use App\Models\IndicadorConsolidado;
use App\Models\Encuesta;
use App\Models\EvaluaProveedores;
use App\Models\Retroalimentacion;
use App\Models\NeceInter;
use App\Models\Registros;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\MacroProceso;


class FormAnalisisDatosController extends Controller
{
    /**
     * Obtener un registro de FormAnalisisDatos junto con sus datos asociados.
     */
    public function getIdRegistro(Request $request)
    {
        Log::info("Consultando Id Registro");
        $request->validate([
            'idRegistro' => 'required|integer'
        ]);

        // Buscar el registro con el apartado "Análisis de Datos"
        $registro = Registros::find($request->idRegistro);
        $proceso = Proceso::where('idProceso', $registro->idProceso)->first();
        Log::info("Consultando Id Registro: {$registro}");
        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró registro de Análisis de Datos para el proceso y año especificados'
            ], 404);
        }
        $entidad = EntidadDependencia::where('idEntidadDependencia', $proceso->idEntidad)->first();
        $macroproceso = Macroproceso::where('idMacroproceso', $proceso->idMacroproceso)->first();


        return response()->json([
            'success' => true,
            'idRegistro' => $registro->idRegistro,
            'proceso' => $proceso,
            'macro' => $macroproceso->tipoMacroproceso,
            'entidad' => $entidad->nombreEntidad,
            'anio' => $registro->año
        ]);

    }

    public function show($registro)
    {
        Log::info("Registro recibido: {$registro}");

        // 1. Obtener el registro actual y su idProceso + año
        $registroActual = Registros::find($registro);
        if (!$registroActual) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $idProceso = $registroActual->idProceso;
        $anio = $registroActual->año;

        // 2. Obtener formulario de análisis de datos
        $formAnalisisDatos = formAnalisisDatos::where('idRegistro', $registro)->get();

        // 3. Obtener los registros de análisisDatos para este idRegistro
        $analisis = AnalisisDatos::where('idRegistro', $registro)->get();

        // 4. Obtener todos los indicadores asociados a este idRegistro (Análisis de Datos)
        $indicador = IndicadorConsolidado::where('idRegistro', $registro)->get();
        $idsIndicadoresConsolidados = $indicador->pluck('idIndicador')->toArray();

        // 5. Obtener datos asociados a indicadores (encuesta, retroalimentación, evaluación)
        $encuesta = Encuesta::whereIn('idIndicador', $idsIndicadoresConsolidados)->get();
        $evaluacion = EvaluaProveedores::whereIn('idIndicador', $idsIndicadoresConsolidados)->get();
        $retroalimentacion = Retroalimentacion::whereIn('idIndicador', $idsIndicadoresConsolidados)->get();

        // 6. Obtener indicadores de GESTIÓN DE RIESGOS (requiere buscar otro idRegistro)
        $registroGestion = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('Apartado', 'Gestión de Riesgo')
            ->first();

        $indicadoresGestion = [];
        if ($registroGestion) {
            $indicadoresGestion = IndicadorConsolidado::where('idRegistro', $registroGestion->idRegistro)->get();
        }

        // 7. Obtener necesidad e interpretación si ya existen
        // 7. Obtener necesidad e interpretación si ya existen (1 idAnalisisDatos → muchas secciones)
        $idAnalisisDatos = optional($analisis->first())->idAnalisisDatos;

        $necesidadInterpretacionFormateada = collect();
        if ($idAnalisisDatos) {
            $necesidades = NeceInter::where('idAnalisisDatos', $idAnalisisDatos)->get();

            Log::info("📌 Necesidades encontradas para idAnalisisDatos {$idAnalisisDatos}", $necesidades->toArray());

            $necesidadInterpretacionFormateada = $necesidades->map(function ($nece) {
                return [
                    'seccion' => $nece->seccion,
                    'Necesidad' => $nece->Necesidad,
                    'Interpretacion' => $nece->Interpretacion,
                ];
            });
        } else {
            Log::warning("⚠️ No se encontró idAnalisisDatos para idRegistro {$registro}");
        }

        // 8. Retornar todo al frontend
        return response()->json([
            'formAnalisisDatos' => $formAnalisisDatos,
            'analisisDatos' => $analisis,
            'indicador' => $indicador,
            'encuesta' => $encuesta,
            'evaluacion' => $evaluacion,
            'retroalimentacion' => $retroalimentacion,
            'gestionRiesgo' => $indicadoresGestion,
            'necesidadInterpretacion' => $necesidadInterpretacionFormateada,
        ]);

    }


    /**
     * Actualizar.
     */
    public function updateNecesidadInterpretacion(Request $request, $idRegistro)
    {
        Log::info("Actualizando necesidad o interpretación");

        $request->validate([
            'seccion' => 'required|string|in:Conformidad,Satisfaccion,Desempeño,Eficacia,Desempeño Proveedores',
            'campo' => 'required|in:necesidad,interpretacion',
            'valor' => 'nullable|string|max:250',
        ]);

        // Obtener idAnalisisDatos relacionado a este idRegistro y sección
        $analisis = AnalisisDatos::where('idRegistro', $idRegistro)->first();


        if (!$analisis) {
            return response()->json(['message' => 'No se encontró análisis de datos para esa sección'], 404);
        }

        $neceInter = NeceInter::firstOrNew([
            'idAnalisisDatos' => $analisis->idAnalisisDatos,
            'seccion' => $request->seccion,
        ]);

        $neceInter->{$request->campo} = $request->valor;
        $neceInter->save();

        return response()->json([
            'message' => 'Información actualizada correctamente',
            'data' => $neceInter
        ]);
    }

    public function guardarAnalisisDatosCompleto(Request $request, $idRegistro)
    {
        Log::info("Guardar análisis completo");

        $request->validate([
            'periodoEvaluacion' => 'required|string|max:50',
            'secciones' => 'required|array',
            'secciones.*.seccion' => 'required|in:Conformidad,Satisfaccion,Desempeño,Eficacia,Desempeño Proveedores',
            'secciones.*.necesidad' => 'nullable|string',
            'secciones.*.interpretacion' => 'nullable|string',
        ]);

        // ✅ 1. Buscaren analisisdatos para el idRegistro
        $analisis = AnalisisDatos::where('idRegistro', $idRegistro)->first();

        if (!$analisis) {
            Log::warning("⚠️ No se encontró registro en AnalisisDatos para idRegistro: {$idRegistro}");
            return response()->json(['message' => 'No se encontró análisis de datos para este registro'], 404);
        }

        Log::info("✅ Registro de AnalisisDatos encontrado: ID = {$analisis->idAnalisisDatos}, Periodo = {$analisis->periodoEvaluacion}");


        // ✅ 2. Aseguramos que se actualiza el periodo de evaluación
        $analisis->periodoEvaluacion = $request->periodoEvaluacion;
        $analisis->save();

        // ✅ 3. Obtener el idAnalisisDatos
        $idAnalisisDatos = $analisis->idAnalisisDatos;

        Log::info("💾 Guardando en NeceInter con idAnalisisDatos: {$idAnalisisDatos}");
        Log::info("📌 Secciones recibidas: " . json_encode($request->input('secciones')));

        // ✅ 4. Crear o actualizar registros en NeceInter
        foreach ($request->input('secciones') as $seccionData) {
            if (!isset($seccionData['seccion']))
                continue;

            NeceInter::updateOrCreate(
                [
                    'idAnalisisDatos' => $idAnalisisDatos,
                    'seccion' => $seccionData['seccion']
                ],
                [
                    'Necesidad' => $seccionData['necesidad'] ?? null,
                    'Interpretacion' => $seccionData['interpretacion'] ?? null
                ]
            );
        }

        return response()->json([
            'message' => 'Datos de análisis actualizados correctamente'
        ]);
    }





}
