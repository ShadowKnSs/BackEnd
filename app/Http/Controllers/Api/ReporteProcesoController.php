<?php

namespace App\Http\Controllers\Api;


use App\Models\MapaProceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Proceso;
use App\Models\Registros;
use App\Models\ActividadControl;
use App\Models\Auditoria;
use App\Models\GestionRiesgos;
use App\Models\Riesgo;
use App\Models\IndicadorConsolidado;
use App\Models\ResultadoIndi;
use App\Models\AnalisisDatos;
use App\Models\ActividadMejora;
use App\Models\SeguimientoMinuta;
use App\Models\Asistente;
use App\Models\ActividadMinuta;
use App\Models\CompromisoMinuta;
use App\Models\ProyectoMejora;
use App\Models\Recurso;
use App\Models\ActividadesPM;
use App\Models\PlanCorrectivo;
use App\Models\ActividadPlan;
use App\Models\NeceInter;
use App\Models\Objetivo;
use App\Models\IndicadoresExito;
use App\Models\ResponsableInv;
use App\Models\ReporteProceso;
use App\Models\EvaluaProveedores;
use App\Models\FuentePt;
use App\Models\PlanTrabajo;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ReporteProcesoController extends Controller
{

    public function destroy($id)
    {
        try {
            $reporte = ReporteProceso::where('idReporteProceso', $id)->first();
            if (!$reporte)
                return response()->json(['error' => 'Reporte no encontrado'], 404);

            // Si tenemos ruta en BD, borra a partir de ella
            if (!empty($reporte->ruta)) {
                // ruta pública: http://host/storage/reportes/xxx.pdf
                $prefix = url('/storage') . '/';
                $relative = str_starts_with($reporte->ruta, $prefix)
                    ? substr($reporte->ruta, strlen($prefix))  // reportes/xxx.pdf
                    : ltrim($reporte->ruta, '/');              // fallback
                Storage::disk('public')->delete($relative);
            } else {
                // Fallback legacy
                $path = "reportes/reporte_proceso_{$reporte->anio}.pdf";
                Storage::disk('public')->delete($path);
            }

            $reporte->delete();
            return response()->json(['message' => 'Reporte eliminado correctamente', 'id' => $id], 200);
        } catch (\Exception $e) {
            \Log::error("Error al eliminar reporte", ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json(['error' => 'Error interno al eliminar el reporte'], 500);
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'idProceso' => 'required|integer|exists:proceso,idProceso',
            'nombreReporte' => 'required|string|max:255',
            'anio' => ['required', 'regex:/^\d{4}$/'],
            'ruta' => 'nullable|url|max:2048',     
        ]);

        try {
            // (Opcional) proteger duplicado por proceso+año desde app (además del índice único)
            $existe = ReporteProceso::where('idProceso', (int) $validated['idProceso'])
                ->where('anio', (int) $validated['anio'])
                ->exists();

            if ($existe) {
                return response()->json([
                    'error' => 'Ya existe un reporte para ese Proceso y Año.'
                ], 409);
            }

            $reporte = new ReporteProceso();
            $reporte->idProceso = (int) $validated['idProceso'];
            $reporte->nombreReporte = $validated['nombreReporte'];
            $reporte->anio = (int) $validated['anio'];
            $reporte->fechaElaboracion = now()->toDateString(); // la columna es DATE
            $reporte->ruta = $validated['ruta'] ?? null; // <- guardar URL si viene
            $reporte->save();

            return response()->json([
                'message' => 'Reporte guardado correctamente',
                'reporte' => $reporte,
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Si pegó en la unique key, devolver 409
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) { // MySQL duplicate entry
                return response()->json([
                    'error' => 'Ya existe un reporte para ese Proceso y Año.'
                ], 409);
            }
            \Log::error("Error SQL al guardar reporte", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al guardar el reporte'], 500);
        } catch (\Throwable $e) {
            \Log::error("Error al guardar reporte", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al guardar el reporte'], 500);
        }
    }



    public function index()
    {
        try {
            // Obtener todos los reportes ordenados por fecha de elaboración (descendente)
            $reportes = ReporteProceso::orderBy('fechaElaboracion', 'desc')->get();
            return response()->json(['reportes' => $reportes], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener reportes', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al obtener reportes'], 500);
        }
    }
    public function generarReporte($idProceso, $anio)
    {
        try {
            $proceso = Proceso::with(['entidad', 'usuario'])->findOrFail($idProceso);
            $mapa = MapaProceso::where('idProceso', $idProceso)->first();
            $planControlActividades = ActividadControl::where('idProceso', $idProceso)->get();
            $auditorias = Auditoria::where('idProceso', $idProceso)->get();
        } catch (\Exception $e) {
            Log::error("Error cargando información del proceso", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al obtener información del proceso'], 500);
        }

        $registro = $this->getRegistro($idProceso, $anio, 'Gestión de Riesgo');
        $gestion = $registro ? GestionRiesgos::where('idRegistro', $registro->idRegistro)->first() : null;
        $riesgos = $gestion ? Riesgo::where('idGesRies', $gestion->idGesRies)->get() : collect();

        $registroIndicadores = $this->getRegistro($idProceso, $anio, 'Análisis de Datos');
        $planControlIndicadores = [];
        $interpretacion = 'No disponible';
        $necesidad = 'No disponible';

        if ($registroIndicadores) {
            $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
                ->where('origenIndicador', 'ActividadControl')->get();
            foreach ($indicadores as $indicador) {
                $resultados = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();

                $planControlIndicadores[] = (object) [
                    'nombreIndicador' => $indicador->nombreIndicador,
                    'meta' => $indicador->meta,
                    'resultadoSemestral1' => $resultados->resultadoSemestral1 ?? null,
                    'resultadoSemestral2' => $resultados->resultadoSemestral2 ?? null,
                ];
            }
            $analisis = AnalisisDatos::where('idRegistro', $registroIndicadores->idRegistro)->first();

            if ($analisis) {
                $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                    ->where('seccion', 'Conformidad')
                    ->first();

                $interpretacion = $neceInter->Interpretacion ?? 'No disponible';
                $necesidad = $neceInter->Necesidad ?? 'No disponible';
            }

        }

        $graficaPlanControl = $this->verificaGrafica("planControl_{$idProceso}_{$anio}");
        $graficaEncuesta = $this->verificaGrafica("encuesta_{$idProceso}_{$anio}");
        $graficaRetroalimentacion = $this->verificaGrafica("retroalimentacion_{$idProceso}_{$anio}");
        $graficaMP = $this->verificaGrafica("mapaProceso_{$idProceso}_{$anio}");
        $graficaRiesgos = $this->verificaGrafica("riesgos_{$idProceso}_{$anio}");
        $graficaEvaluacion = $this->verificaGrafica("evaluacionProveedores_{$idProceso}_{$anio}");

        $registroSeg = $this->getRegistro($idProceso, $anio, 'Seguimiento');
        $seguimientos = $registroSeg ? SeguimientoMinuta::where('idRegistro', $registroSeg->idRegistro)->get() : collect();
        $idSeguimientos = $seguimientos->pluck('idSeguimiento')->toArray();
        $asistentes = Asistente::whereIn('idSeguimiento', $idSeguimientos)->get();
        $actividadesSeg = ActividadMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();
        $compromisosSeg = CompromisoMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();

        $registroAcMejora = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Acciones de Mejora')
            ->first();

        if (!$registroAcMejora) {
            return response()->json(['error' => 'No se encontró el registro.'], 404);
        }

        $acMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();
        $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
        $proyectoMejora = ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();
        $recursos = optional($proyectoMejora)->idProyectoMejora ? Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get() : collect();
        $actividadesPM = optional($proyectoMejora)->idProyectoMejora ? ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get() : collect();
        $objetivos = optional($proyectoMejora)->idProyectoMejora
            ? Objetivo::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get()
            : collect();

        $responsablesInv = optional($proyectoMejora)->idProyectoMejora
            ? ResponsableInv::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get()
            : collect();

        $indicadoresExito = optional($proyectoMejora)->idProyectoMejora
            ? IndicadoresExito::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get()
            : collect();

        $planCorrectivo = PlanCorrectivo::whereIn('idActividadMejora', $idAccMejora)->first();
        $actividadesPlan = optional($planCorrectivo)->idPlanCorrectivo ? ActividadPlan::where('idPlanCorrectivo', $planCorrectivo->idPlanCorrectivo)->get() : collect();

        $indicadoresJson = $this->indicadoresSatisfaccionCliente($idProceso, $anio)->getContent();
        $indicadoresSatisfaccion = json_decode($indicadoresJson, true);

        $indicadoresMP = IndicadorConsolidado::where('idProceso', $idProceso)
            ->where('origenIndicador', 'MapaProceso')->get();
        $resultadoMP = $indicadoresMP->map(function ($indicador) {
            $res = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();
            return (object) [
                'nombreIndicador' => $indicador->nombreIndicador,
                'meta' => $indicador->meta,
                'resultadoSemestral1' => $res->resultadoSemestral1 ?? 0,
                'resultadoSemestral2' => $res->resultadoSemestral2 ?? 0
            ];
        });

        $interpretacionMP = $interpretacion;
        $necesidadMP = $necesidad;
        $evaluacionProveedores = $this->getEvaluacionProveedoresData($idProceso, $anio);
        $proyectoMejoraData = $this->getProyectoMejoraData($idProceso, $anio);
        // Obtener datos del plan de trabajo
// Obtener datos del plan de trabajo
        $planTrabajoData = $this->obtenerPlanTrabajoData($idProceso, $anio);

        $datos = [
            'nombreProceso' => $proceso->nombreProceso,
            'entidad' => $proceso->entidad->nombreEntidad ?? 'Entidad no disponible',
            'liderProceso' => $proceso->usuario->nombre ?? 'Líder no asignado',
            'objetivo' => $proceso->objetivo ?? 'No especificado',
            'alcance' => $proceso->alcance ?? 'No especificado',
            'norma' => $proceso->norma ?? 'No especificado',
            'anioCertificacion' => $proceso->anioCertificado ?? 'No especificado',
            'estado' => $proceso->estado ?? 'No especificado',
            'documentos' => $mapa->documentos ?? 'No disponible',
            'puestosInvolucrados' => $mapa->puestosInvolucrados ?? 'No disponible',
            'fuente' => $mapa->fuente ?? 'No disponible',
            'material' => $mapa->material ?? 'No disponible',
            'requisitos' => $mapa->requisitos ?? 'No disponible',
            'salidas' => $mapa->salidas ?? 'No disponible',
            'receptores' => $mapa->receptores ?? 'No disponible',
            'diagramaFlujo' => $mapa->diagramaFlujo ?? 'No disponible',
            'planControlActividades' => $planControlActividades,
            'planControlIndicadores' => $planControlIndicadores,
            'interpretacionPlanControl' => $interpretacion,
            'necesidadPlanControl' => $necesidad,
            'planControl' => $planControlActividades,
            'auditorias' => $auditorias,
            'riesgos' => $riesgos,
            'graficaPlanControl' => $graficaPlanControl,
            'graficaEncuesta' => $graficaEncuesta,
            'graficaRetroalimentacion' => $graficaRetroalimentacion,
            'graficaMP' => $graficaMP,
            'graficaRiesgos' => $graficaRiesgos,
            'graficaEvaluacion' => $graficaEvaluacion,
            'seguimientos' => $seguimientos,
            'idseguimientos' => $idSeguimientos,
            'asistentes' => $asistentes,
            'actividadesSeg' => $actividadesSeg,
            'compromisosSeg' => $compromisosSeg,
            'Accion Mejora' => $acMejora,
            'idAcciones' => $idAccMejora,
            'proyectoMejora' => $proyectoMejora,
            'recursos' => $recursos,
            'actividadesPM' => $actividadesPM,
            'proyectoObjetivos' => $objetivos,
            'proyectoResponsables' => $responsablesInv,
            'proyectoIndicadoresExito' => $indicadoresExito,
            'planCorrectivo' => $planCorrectivo,
            'actividadesPlan' => $actividadesPlan,
            'indicadoresSatisfaccion' => $indicadoresSatisfaccion,
            'mapaProcesoIndicadores' => $resultadoMP,
            'interpretacionMapaProceso' => $interpretacionMP,
            'necesidadMapaProceso' => $necesidadMP,
            'evaluacionProveedores' => $evaluacionProveedores,
            'proyectoMejoraData' => $proyectoMejoraData,
            'planTrabajoData' => $planTrabajoData,

        ];

        try {
            $pdf = Pdf::loadView('proceso', $datos);

            // Nombre de archivo: Entidad_Proceso_Año.pdf
            $entidadNombre = $proceso->entidad->nombreEntidad ?? 'Entidad';
            $procesoNombre = $proceso->nombreProceso ?? 'Proceso';
            $filename = Str::slug($entidadNombre, '_') . '_' . Str::slug($procesoNombre, '_') . '_' . $anio . '.pdf';

            // Guardar en storage/app/public/reportes (sin subcarpetas por id/año)
            $dir = 'reportes';
            $path = "{$dir}/{$filename}";
            // Opcional, por claridad (Storage::put crea el dir si no existe)
            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put($path, $pdf->output());
            $publicUrl = Storage::disk('public')->url($path);

            // Devolver el PDF como adjunto (el front ya usa responseType:"blob")
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'X-Report-URL' => $publicUrl,            // <- header con la URL
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ]);
            ;
        } catch (\Throwable $e) {
            Log::error("Error al generar PDF", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }

    }

    private function getRegistro($idProceso, $anio, $apartado)
    {
        return Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', $apartado)
            ->first();
    }

    private function verificaGrafica($filename)
    {
        foreach (['png', 'jpg', 'jpeg', 'gif'] as $ext) {
            $path = public_path("storage/graficas/{$filename}.{$ext}");
            if (file_exists($path))
                return $path;
        }
        return null;
    }


    public function obtenerDatosReporte($idProceso, $anio)
    {
        try {
            // Obtener el proceso con la entidad y usuario líder
            $proceso = Proceso::with(['entidad', 'usuario'])->where('idProceso', $idProceso)->firstOrFail();
            // $mapaProceso = MapaProceso::where('idProceso', $idProceso)->get();

            $liderProceso = 'Líder no asignado';
            if (isset($proceso->usuario)) {
                $liderProceso = $proceso->usuario->nombre;
                // Si existen ambos apellidos, los concatenamos
                if (isset($proceso->usuario->apellidoPat) && isset($proceso->usuario->apellidoMat)) {
                    $liderProceso .= ' ' . $proceso->usuario->apellidoPat . ' ' . $proceso->usuario->apellidoMat;
                }
            }

            return response()->json([
                'nombreProceso' => $proceso->nombreProceso,
                'entidad' => $proceso->entidad->nombreEntidad ?? 'Entidad no disponible',
                'liderProceso' => $liderProceso,
                'objetivo' => $proceso->objetivo ?? 'No especificado',
                'alcance' => $proceso->alcance ?? 'No especificado',
                'norma' => $proceso->norma ?? 'No especificado',
                'anioCertificacion' => $proceso->anioCertificado ?? 'No especificado',
                'estado' => $proceso->estado ?? 'No especificacccdo',
            ]);


        } catch (\Exception $e) {
            return response()->json(['error' => 'Datos no encontrados'], 404);
        }
    }

    public function obtenerMapaProceso($idProceso)
    {
        try {
            $mapaProceso = MapaProceso::where('idProceso', $idProceso)->first();

            if (!$mapaProceso) {
                return response()->json(['error' => 'No se encontró información del Mapa de Proceso'], 404);
            }

            return response()->json([
                'documentos' => $mapaProceso->documentos ?? 'No disponible',
                'puestosInvolucrados' => $mapaProceso->puestosInvolucrados ?? 'No disponible',
                'fuente' => $mapaProceso->fuente ?? 'No disponible',
                'material' => $mapaProceso->material ?? 'No disponible',
                'requisitos' => $mapaProceso->requisitos ?? 'No disponible',
                'salidas' => $mapaProceso->salidas ?? 'No disponible',
                'receptores' => $mapaProceso->receptores ?? 'No disponible',
                'diagramaFlujo' => $mapaProceso->diagramaFlujo ?? 'No disponible',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el Mapa de Proceso'], 500);
        }
    }

    public function obtenerAuditoria($idProceso)
    {
        try {
            $auditorias = Auditoria::where('idProceso', $idProceso)->get();

            if ($auditorias->isEmpty()) {
                return response()->json(['error' => 'No se encontraron auditorías'], 404);
            }

            return response()->json($auditorias);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las auditorías'], 500);
        }
    }

    public function obtenerRiesgosPorProcesoYAnio($idProceso, $anio)
    {
        try {
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Gestión de Riesgo')
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'No se encontró el registro.'], 404);
            }

            $gestion = GestionRiesgos::where('idRegistro', $registro->idRegistro)->first();
            if (!$gestion) {
                return response()->json(['error' => 'No se encontró gestión de riesgos.'], 404);
            }

            $riesgos = Riesgo::where('idGesRies', $gestion->idGesRies)->get();

            return response()->json([
                'riesgos' => $riesgos,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos'], 500);
        }
    }


    public function indicadoresSatisfaccionCliente($idProceso, $anio)
    {
        try {
            // Obtener idRegistro del apartado "AnálisisDatos"
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Análisis de Datos')
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            // Buscar interpretación y necesidad para la sección "Satisfacción"
            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->first();

            $neceInter = null;
            if ($analisis) {
                $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                    ->where('seccion', 'Satisfaccion')
                    ->first();
            }

            $interpretacion = $neceInter->Interpretacion ?? null;
            $necesidad = $neceInter->Necesidad ?? null;

            // Buscar indicadores del tipo Encuesta y Retroalimentación
            $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
                ->whereIn('origenIndicador', ['Encuesta', 'Retroalimentacion'])
                ->get();

            $resultado = [];

            foreach ($indicadores as $indicador) {
                $base = [
                    'idIndicador' => $indicador->idIndicador,
                    'nombreIndicador' => $indicador->nombreIndicador,
                    'origen' => $indicador->origenIndicador,
                    'meta' => $indicador->meta,
                    'interpretacion' => $interpretacion,
                    'necesidad' => $necesidad,
                ];

                if ($indicador->origenIndicador === 'Encuesta') {
                    $encuesta = \App\Models\Encuesta::where('idIndicador', $indicador->idIndicador)->first();

                    $total = (int) ($encuesta->noEncuestas ?? 0);
                    $malo = (int) ($encuesta->malo ?? 0);
                    $reg = (int) ($encuesta->regular ?? 0);
                    $bueno = (int) ($encuesta->bueno ?? 0);
                    $excel = (int) ($encuesta->excelente ?? 0);
                    $porc = $total > 0 ? round((($bueno + $excel) * 100) / $total, 2) : 0;

                    $base += [
                        'noEncuestas' => $total,
                        'malo' => $malo,
                        'regular' => $reg,
                        'bueno' => $bueno,
                        'excelente' => $excel,
                        'porcentajeEB' => $porc,
                    ];
                } elseif ($indicador->origenIndicador === 'Retroalimentacion') {
                    $retro = \App\Models\Retroalimentacion::where('idIndicador', $indicador->idIndicador)->first();

                    $fel = (int) ($retro->cantidadFelicitacion ?? 0);
                    $sug = (int) ($retro->cantidadSugerencia ?? 0);
                    $que = (int) ($retro->cantidadQueja ?? 0);
                    $tot = $fel + $sug + $que;

                    $base += [
                        'felicitaciones' => $fel,
                        'sugerencias' => $sug,
                        'quejas' => $que,
                        'total' => $tot,
                    ];
                }

                $resultado[] = $base;
            }

            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            \Log::error(" Error en indicadoresSatisfaccionCliente:", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function obtenerSeguimiento($idProceso, $anio)
    {
        try {

            $registroSeg = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Seguimiento')
                ->first();

            if (!$registroSeg) {
                return response()->json(['error' => 'No se encontró el registro.'], 404);
            }
            // Obtener los seguimientos relacionados
            $seguimientos = SeguimientoMinuta::where('idRegistro', $registroSeg->idRegistro)->get();

            if ($seguimientos->isEmpty()) {
                return response()->json(['error' => 'No se encontraron seguimientos para este proceso.'], 404);
            }

            $idSeguimientos = $seguimientos->pluck('idSeguimiento')->toArray();

            // Obtener los asistentes, actividades y compromisos relacionados con los seguimientos
            $asistentes = Asistente::whereIn('idSeguimiento', $idSeguimientos)->get();
            $actividadesSeg = ActividadMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();
            $compromisosSeg = CompromisoMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();

            return response()->json([
                'seguimientos' => $seguimientos,
                'asistentes' => $asistentes,
                'actividades' => $actividadesSeg,
                'compromisos' => $compromisosSeg,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los seguimientos', 'detalle' => $e->getMessage()], 500);
        }
    }
    public function obtenerPM($idProceso, $anio)
    {
        try {
            $registroAcMejora = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Acciones de Mejora')
                ->first();

            if (!$registroAcMejora) {
                return response()->json(['error' => 'No se encontró el registro.'], 404);
            }

            $acMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();
            $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
            $proyectoMejora = ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();

            if (!$proyectoMejora) {
                return response()->json(['error' => 'No hay proyecto de mejora asociado.'], 404);
            }

            $recursos = Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $actividadesPM = ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $objetivos = Objetivo::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $responsables = ResponsableInv::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $indicadoresExito = IndicadoresExito::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();

            return response()->json([
                'acMejora' => $acMejora,
                'proyectoMejora' => $proyectoMejora,
                'recursos' => $recursos,
                'actividadesPM' => $actividadesPM,
                'objetivos' => $objetivos,
                'responsables' => $responsables,
                'indicadoresExito' => $indicadoresExito,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function obtenerPlanCorrectivo($idProceso, $anio)
    {
        try {

            $registroAcMejora = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Acciones de Mejora')
                ->first();

            if (!$registroAcMejora) {
                return response()->json(['error' => 'No se encontró el registro.'], 404);
            }
            // Obtener los seguimientos relacionados
            $acMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();

            $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
            $planCorrectivo = PlanCorrectivo::whereIn('idActividadMejora', $idAccMejora)->first();
            $actividadesPlan = ActividadPlan::where('idPlanCorrectivo', $planCorrectivo->idPlanCorrectivo)->get();
            return response()->json([
                'acMejora' => $acMejora,
                'planCorrectivo' => $planCorrectivo,
                'actividadesPlan' => $actividadesPlan
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function indicadoresMapaProceso($idProceso, $anio)
    {
        try {
            // Buscar el registro de Análisis de Datos para obtener idAnalisisDatos
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Análisis de Datos')
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->first();

            $neceInter = null;
            if ($analisis) {
                $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                    ->where('seccion', 'Desempeño')
                    ->first();
            }

            $interpretacion = $neceInter->Interpretacion ?? 'No disponible';
            $necesidad = $neceInter->Necesidad ?? 'No disponible';

            // Obtener indicadores del tipo MapaProceso
            $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
                ->where('origenIndicador', 'MapaProceso')
                ->get();

            $resultado = $indicadores->map(function ($indicador) use ($interpretacion, $necesidad) {
                $res = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();

                return [
                    'idIndicador' => $indicador->idIndicador,
                    'nombreIndicador' => $indicador->nombreIndicador,
                    'meta' => $indicador->meta,
                    'resultadoSemestral1' => $res->resultadoSemestral1 ?? 0,
                    'resultadoSemestral2' => $res->resultadoSemestral2 ?? 0,
                    'interpretacion' => $interpretacion,
                    'necesidad' => $necesidad,
                ];
            });

            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            \Log::error('Error en indicadoresMapaProceso()', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function eficaciaRiesgos($idProceso, $anio)
    {
        try {
            // 1. Buscar el registro correspondiente al apartado Gestión de Riesgos
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Gestión de Riesgo')
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            // 2. Buscar análisis y su relación con NeceInter para la sección Eficacia
            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->first();
            $neceInter = null;

            if ($analisis) {
                $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                    ->where('seccion', 'Eficacia')
                    ->first();
            }

            $interpretacion = $neceInter->Interpretacion ?? 'No disponible';
            $necesidad = $neceInter->Necesidad ?? 'No disponible';

            // 3. Obtener indicadores asociados a este registro
            $indicadores = IndicadorConsolidado::where('idRegistro', $registro->idRegistro)->get();

            $resultado = $indicadores->map(function ($indicador) use ($interpretacion, $necesidad) {
                $res = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();

                return [
                    'idIndicador' => $indicador->idIndicador,
                    'nombreIndicador' => $indicador->nombreIndicador,
                    'meta' => $indicador->meta,
                    'resultadoAnual' => $res->resultadoAnual ?? 0,
                    'interpretacion' => $interpretacion,
                    'necesidad' => $necesidad,
                ];
            });

            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            \Log::error("❌ Error en eficaciaRiesgos()", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno al obtener los indicadores de eficacia'], 500);
        }
    }

    public function evaluacionProveedores($idProceso, $anio)
    {
        try {
            // 1. Buscar el registro del apartado Análisis de Datos
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Análisis de Datos')
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            // 2. Obtener el análisis y NeceInter para "Desempeño Proveedores"
            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->first();
            $neceInter = null;

            if ($analisis) {
                $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                    ->where('seccion', 'Desempeño Proveedores')
                    ->first();
            }

            $interpretacion = $neceInter->Interpretacion ?? 'No disponible';
            $necesidad = $neceInter->Necesidad ?? 'No disponible';

            // 3. Obtener indicadores del tipo EvaluaProveedores
            $indicadores = IndicadorConsolidado::where('idRegistro', $registro->idRegistro)
                ->where('origenIndicador', 'EvaluaProveedores')
                ->get();

            $resultado = $indicadores->map(function ($indicador) use ($interpretacion, $necesidad) {
                $eval = EvaluaProveedores::where('idIndicador', $indicador->idIndicador)->first();

                return [
                    'idIndicador' => $indicador->idIndicador,
                    'nombreIndicador' => $indicador->nombreIndicador,
                    'meta' => $indicador->meta,
                    'metaConfiable' => $eval->metaConfiable ?? 0,
                    'metaCondicionado' => $eval->metaCondicionado ?? 0,
                    'metaNoConfiable' => $eval->metaNoConfiable ?? 0,
                    'resultadoConfiableSem1' => $eval->resultadoConfiableSem1 ?? 0,
                    'resultadoConfiableSem2' => $eval->resultadoConfiableSem2 ?? 0,
                    'resultadoCondicionadoSem1' => $eval->resultadoCondicionadoSem1 ?? 0,
                    'resultadoCondicionadoSem2' => $eval->resultadoCondicionadoSem2 ?? 0,
                    'resultadoNoConfiableSem1' => $eval->resultadoNoConfiableSem1 ?? 0,
                    'resultadoNoConfiableSem2' => $eval->resultadoNoConfiableSem2 ?? 0,
                    'interpretacion' => $interpretacion,
                    'necesidad' => $necesidad,
                ];
            });

            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            \Log::error("❌ Error en evaluacionProveedores()", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno al obtener evaluación de proveedores'], 500);
        }
    }

    private function getEvaluacionProveedoresData($idProceso, $anio)
    {
        try {
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Análisis de Datos')
                ->first();

            if (!$registro) {
                return null;
            }

            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->first();
            $neceInter = null;

            if ($analisis) {
                $neceInter = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                    ->where('seccion', 'Desempeño Proveedores')
                    ->first();
            }

            $interpretacion = $neceInter->Interpretacion ?? 'No disponible';
            $necesidad = $neceInter->Necesidad ?? 'No disponible';

            $indicadores = IndicadorConsolidado::where('idRegistro', $registro->idRegistro)
                ->where('origenIndicador', 'EvaluaProveedores')
                ->get();

            $categorias = [];

            foreach ($indicadores as $indicador) {
                $eval = EvaluaProveedores::where('idIndicador', $indicador->idIndicador)->first();

                if ($eval) {
                    $categorias[] = [
                        'categoria' => 'Confiable',
                        'meta' => $eval->metaConfiable ?? 0,
                        'resultado1' => $eval->resultadoConfiableSem1 ?? 0,
                        'resultado2' => $eval->resultadoConfiableSem2 ?? 0,
                    ];
                    $categorias[] = [
                        'categoria' => 'Condicionado',
                        'meta' => $eval->metaCondicionado ?? 0,
                        'resultado1' => $eval->resultadoCondicionadoSem1 ?? 0,
                        'resultado2' => $eval->resultadoCondicionadoSem2 ?? 0,
                    ];
                    $categorias[] = [
                        'categoria' => 'No Confiable',
                        'meta' => $eval->metaNoConfiable ?? 0,
                        'resultado1' => $eval->resultadoNoConfiableSem1 ?? 0,
                        'resultado2' => $eval->resultadoNoConfiableSem2 ?? 0,
                    ];
                }
            }

            return [
                'indicadores' => $categorias,
                'interpretacion' => $interpretacion,
                'necesidad' => $necesidad
            ];
        } catch (\Exception $e) {
            \Log::error("❌ Error en getEvaluacionProveedoresData()", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getProyectoMejoraData($idProceso, $anio)
    {
        try {
            $registroAcMejora = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Acciones de Mejora')
                ->first();

            if (!$registroAcMejora) {
                return null;
            }

            $acMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();
            $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
            $proyectoMejora = ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();

            if (!$proyectoMejora) {
                return null;
            }

            $recursos = Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $actividadesPM = ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $objetivos = Objetivo::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $responsables = ResponsableInv::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $indicadoresExito = IndicadoresExito::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();

            return [
                'acMejora' => $acMejora,
                'proyectoMejora' => $proyectoMejora,
                'recursos' => $recursos,
                'actividadesPM' => $actividadesPM,
                'objetivos' => $objetivos,
                'responsables' => $responsables,
                'indicadoresExito' => $indicadoresExito,
            ];
        } catch (\Exception $e) {
            \Log::error("❌ Error en getProyectoMejoraData()", ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function obtenerPlanTrabajo($idProceso, $anio)
    {
        try {
            // Buscar el registro de Acciones de Mejora
            $registroAcMejora = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Acciones de Mejora')
                ->first();

            if (!$registroAcMejora) {
                return response()->json(['error' => 'No se encontró el registro de Acciones de Mejora.'], 404);
            }

            // Obtener las actividades de mejora
            $actividadesMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();

            if ($actividadesMejora->isEmpty()) {
                return response()->json(['error' => 'No se encontraron actividades de mejora.'], 404);
            }

            $idActividadesMejora = $actividadesMejora->pluck('idActividadMejora')->toArray();

            // Obtener el plan de trabajo (solo uno) - usar first() en lugar de get()
            $planTrabajo = PlanTrabajo::whereIn('idActividadMejora', $idActividadesMejora)->first();

            if (!$planTrabajo) {
                return response()->json(['error' => 'No se encontró el plan de trabajo.'], 404);
            }

            // Obtener las fuentes para el plan de trabajo
            $fuentes = FuentePt::where('idPlanTrabajo', $planTrabajo->idPlanTrabajo)->get();

            $resultado = [
                'planTrabajo' => $planTrabajo,
                'fuentes' => $fuentes
            ];

            return response()->json($resultado);
        } catch (\Exception $e) {
            \Log::error(" Error en obtenerPlanTrabajo()", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno al obtener el plan de trabajo'], 500);
        }
    }


    private function obtenerPlanTrabajoData($idProceso, $anio)
    {
        try {
            // Buscar el registro de Acciones de Mejora
            $registroAcMejora = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Acciones de Mejora')
                ->first();

            if (!$registroAcMejora) {
                return null;
            }

            // Obtener las actividades de mejora
            $actividadesMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();

            if ($actividadesMejora->isEmpty()) {
                return null;
            }

            $idActividadesMejora = $actividadesMejora->pluck('idActividadMejora')->toArray();

            // Obtener el plan de trabajo (solo uno) - usar first() en lugar de get()
            $planTrabajo = PlanTrabajo::whereIn('idActividadMejora', $idActividadesMejora)->first();

            if (!$planTrabajo) {
                return null;
            }

            // Obtener las fuentes para el plan de trabajo
            $fuentes = FuentePt::where('idPlanTrabajo', $planTrabajo->idPlanTrabajo)->get();

            $resultado = [
                'planTrabajo' => $planTrabajo,
                'fuentes' => $fuentes
            ];

            return $resultado;
        } catch (\Exception $e) {
            \Log::error(" Error en obtenerPlanTrabajoData()", ['error' => $e->getMessage()]);
            return null;
        }
    }
}
