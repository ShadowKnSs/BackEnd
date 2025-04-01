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
use App\Models\ReporteProceso;

use App\Models\EvaluaProveedores;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ReporteProcesoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idProceso' => 'required|integer',
            'nombreReporte' => 'required|string|max:255',
        ]);

        try {
            $reporte = new ReporteProceso();
            $reporte->idProceso = $validated['idProceso'];
            $reporte->nombreReporte = $validated['nombreReporte'];
            $reporte->fechaElaboracion = now(); // Se asigna la fecha actual
            $reporte->save();

            return response()->json([
                'message' => 'Reporte guardado correctamente',
                'reporte' => $reporte,
            ], 201);
        } catch (\Exception $e) {
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
            $analisis = AnalisisDatos::where('idRegistro', $registroIndicadores->idRegistro)
                ->where('seccion', 'Conformidad')->first();
            if ($analisis) {
                $interpretacion = $analisis->interpretacion ?? 'No disponible';
                $necesidad = $analisis->necesidad ?? 'No disponible';
            }
        }

        $graficaPlanControl = $this->verificaGrafica("plan_control_{$idProceso}_{$anio}.png");
        $graficaEncuesta = $this->verificaGrafica("encuesta_{$idProceso}_{$anio}.png");
        $graficaRetroalimentacion = $this->verificaGrafica("retroalimentacion_{$idProceso}_{$anio}.png");
        $graficaMP = $this->verificaGrafica("mapaProceso_{$idProceso}_{$anio}.png");
        $graficaRiesgos = $this->verificaGrafica("riesgos_{$idProceso}_{$anio}.png");
        $graficaEvaluacion = $this->verificaGrafica("evaluacionProveedores_{$idProceso}_{$anio}.png");

        $registroSeg = $this->getRegistro($idProceso, $anio, 'Seguimiento');
        $seguimientos = $registroSeg ? SeguimientoMinuta::where('idRegistro', $registroSeg->idRegistro)->get() : collect();
        $idSeguimientos = $seguimientos->pluck('idSeguimiento')->toArray();
        $asistentes = Asistente::whereIn('idSeguimiento', $idSeguimientos)->get();
        $actividadesSeg = ActividadMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();
        $compromisosSeg = CompromisoMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();

        $registroAcMejora = $this->getRegistro($idProceso, $anio, 'Acciones de Mejora');
        $acMejora = $registroAcMejora ? ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get() : collect();
        $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
        $proyectoMejora = ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();
        $recursos = optional($proyectoMejora)->idProyectoMejora ? Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get() : collect();
        $actividadesPM = optional($proyectoMejora)->idProyectoMejora ? ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get() : collect();
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
            'planCorrectivo' => $planCorrectivo,
            'actividadesPlan' => $actividadesPlan,
            'indicadoresSatisfaccion' => $indicadoresSatisfaccion,
            'mapaProcesoIndicadores' => $resultadoMP,
            'interpretacionMapaProceso' => $interpretacionMP,
            'necesidadMapaProceso' => $necesidadMP,
        ];

        try {
            $pdf = Pdf::loadView('proceso', $datos);
            return $pdf->download("reporte_proceso_{$anio}.pdf");
        } catch (\Exception $e) {
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
        $path = public_path("storage/graficas/$filename");
        return file_exists($path) ? $path : null;
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
            // 🔍 Obtener idRegistro del apartado "AnálisisDatos"
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Análisis de Datos')
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            // 🔎 Buscar interpretación y necesidad para la sección "Satisfacción"
            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)
                ->where('seccion', 'Satisfacción')
                ->first();

            $interpretacion = $analisis->interpretacion ?? null;
            $necesidad = $analisis->necesidad ?? null;

            // 🔄 Buscar indicadores del tipo Encuesta y Retroalimentación
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

                    if ($encuesta) {
                        $total = $encuesta->noEncuestas ?? 0;
                        $excelenteBueno = ($encuesta->bueno + $encuesta->excelente);
                        $porcentaje = $total > 0 ? round(($excelenteBueno * 100) / $total, 2) : 0;

                        $base += [
                            'noEncuestas' => $total,
                            'malo' => $encuesta->malo,
                            'regular' => $encuesta->regular,
                            'bueno' => $encuesta->bueno,
                            'excelente' => $encuesta->excelente,
                            'porcentajeEB' => $porcentaje,
                        ];
                    }
                } elseif ($indicador->origenIndicador === 'Retroalimentacion') {
                    $retro = \App\Models\Retroalimentacion::where('idIndicador', $indicador->idIndicador)->first();

                    if ($retro) {
                        $total = $retro->cantidadFelicitacion + $retro->cantidadSugerencia + $retro->cantidadQueja;

                        $base += [
                            'felicitaciones' => $retro->cantidadFelicitacion,
                            'sugerencias' => $retro->cantidadSugerencia,
                            'quejas' => $retro->cantidadQueja,
                            'total' => $total
                        ];
                    }
                }

                $resultado[] = $base;
            }

            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            \Log::error("❌ Error en indicadoresSatisfaccionCliente:", ['error' => $e->getMessage()]);
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
            // Obtener los seguimientos relacionados
            $acMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();

            $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
            $proyectoMejora = ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();
            $recursos = Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $actividadesPM = ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            return response()->json([
                'acMejora' => $acMejora,
                'proyectoMejora' => $proyectoMejora,
                'recursos' => $recursos,
                'actividadesPM' => $actividadesPM,
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
        Log::info("🔍 Obteniendo indicadores MapaProceso", compact('idProceso', 'anio'));

        // Obtener indicadores de origen MapaProceso
        $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
            ->where('origenIndicador', 'MapaProceso')
            ->get();

        // Obtener resultados
        $resultados = ResultadoIndi::whereIn('idIndicador', $indicadores->pluck('idIndicador'))->get()
            ->keyBy('idIndicador');

        // Buscar idRegistro para análisis
        $registro = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Análisis de Datos')
            ->first();

        $interpretacion = null;
        $necesidad = null;

        if ($registro) {
            $interpretacion = AnalisisDatos::where('idRegistro', $registro->idRegistro)
                ->where('seccion', 'DesempeñoProceso')
                ->value('interpretacion');

            $necesidad = AnalisisDatos::where('idRegistro', $registro->idRegistro)
                ->where('seccion', 'DesempeñoProceso')
                ->value('necesidad');
        }

        // Estructura final
        $datos = $indicadores->map(function ($indicador) use ($resultados, $interpretacion, $necesidad) {
            $res = $resultados[$indicador->idIndicador] ?? null;
            return [
                'idIndicador' => $indicador->idIndicador,
                'nombreIndicador' => $indicador->nombreIndicador,
                'meta' => $indicador->meta,
                'resultadoSemestral1' => $res->resultadoSemestral1 ?? null,
                'resultadoSemestral2' => $res->resultadoSemestral2 ?? null,
                'interpretacion' => $interpretacion,
                'necesidad' => $necesidad,
            ];
        });

        return response()->json($datos);
    }

    public function eficaciaRiesgos($idProceso, $anio)
    {
        $registro = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Análisis de Datos')
            ->first();

        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)
            ->where('seccion', 'Eficacia')
            ->first();

        $interpretacion = $analisis->interpretacion ?? null;
        $necesidad = $analisis->necesidad ?? null;

        $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
            ->where('origenIndicador', 'GestionRiesgo')
            ->get();

        $datos = $indicadores->map(function ($indicador) use ($interpretacion, $necesidad) {
            $result = ResultadoIndi::where('idIndicador', $indicador->idIndicador)->first();
            return [
                'idIndicador' => $indicador->idIndicador,
                'nombreIndicador' => $indicador->nombreIndicador,
                'meta' => $indicador->meta,
                'resultadoAnual' => $result->resultadoAnual ?? null,
                'interpretacion' => $interpretacion,
                'necesidad' => $necesidad,
            ];
        });

        return response()->json($datos);
    }

    public function evaluacionProveedores($idProceso, $anio)
    {
        try {
            Log::info("📥 Inicio de evaluación de proveedores", [
                'idProceso' => $idProceso,
                'anio' => $anio
            ]);

            // 1. Buscar idRegistro para sección AnálisisDatos
            $registro = Registros::where('idProceso', $idProceso)
                ->where('año', $anio)
                ->where('apartado', 'Análisis de Datos')
                ->first();

            if (!$registro) {
                Log::warning("⚠️ Registro no encontrado", ['idProceso' => $idProceso, 'anio' => $anio]);
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }

            Log::info("✅ Registro encontrado", ['idRegistro' => $registro->idRegistro]);

            // 2. Obtener interpretación y necesidad de mejora de la sección "DesempeñoProveedores"
            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)
                ->where('seccion', 'DesempeñoProveedores')
                ->first();

            $interpretacion = $analisis->interpretacion ?? null;
            $necesidad = $analisis->necesidad ?? null;

            Log::info("📌 Interpretación y necesidad obtenidas", [
                'interpretacion' => $interpretacion,
                'necesidad' => $necesidad
            ]);

            // 3. Buscar el indicador tipo EvaluaProveedores
            $indicador = IndicadorConsolidado::where('idProceso', $idProceso)
                ->where('origenIndicador', 'EvaluaProveedores')
                ->first();

            if (!$indicador) {
                Log::warning("⚠️ Indicador no encontrado para EvaluaProveedores");
                return response()->json(['error' => 'Indicador no encontrado'], 404);
            }

            Log::info("✅ Indicador encontrado", ['idIndicador' => $indicador->idIndicador]);

            // 4. Obtener los datos de evaluación desde la tabla específica
            $resultados = EvaluaProveedores::where('idIndicador', $indicador->idIndicador)->first();

            if (!$resultados) {
                Log::warning("⚠️ Resultados de evaluación no encontrados");
                return response()->json(['error' => 'Resultados no encontrados'], 404);
            }

            Log::info("📊 Resultados obtenidos", [
                'confiable' => [$resultados->resultadoConfiableSem1, $resultados->resultadoConfiableSem2],
                'condicionado' => [$resultados->resultadoCondicionadoSem1, $resultados->resultadoCondicionadoSem2],
                'noConfiable' => [$resultados->resultadoNoConfiableSem1, $resultados->resultadoNoConfiableSem2]
            ]);

            // 5. Formato por categoría
            $datos = [
                [
                    'categoria' => 'Confiable',
                    'meta' => $resultados->metaConfiable,
                    'resultado1' => $resultados->resultadoConfiableSem1,
                    'resultado2' => $resultados->resultadoConfiableSem2,
                ],
                [
                    'categoria' => 'Condicionado',
                    'meta' => $resultados->metaCondicionado,
                    'resultado1' => $resultados->resultadoCondicionadoSem1,
                    'resultado2' => $resultados->resultadoCondicionadoSem2,
                ],
                [
                    'categoria' => 'No Confiable',
                    'meta' => $resultados->metaNoConfiable,
                    'resultado1' => $resultados->resultadoNoConfiableSem1,
                    'resultado2' => $resultados->resultadoNoConfiableSem2,
                ]
            ];

            Log::info("📤 Enviando datos de evaluación de proveedores");

            return response()->json([
                'indicadores' => $datos,
                'interpretacion' => $interpretacion,
                'necesidad' => $necesidad
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en evaluacionProveedores', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al obtener evaluación de proveedores'], 500);
        }
    }


    public function destroy($idReporteProceso)
    {
        try {
            $reporte = ReporteProceso::findOrFail($idReporteProceso);
            $reporte->delete();
            return response()->json(['message' => 'Reporte eliminado correctamente'], 200);
        } catch (\Exception $e) {
            \Log::error("Error al eliminar el reporte", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar el reporte'], 500);
        }
    }

}
