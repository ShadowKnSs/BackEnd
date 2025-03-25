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

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;


class ReporteProcesoController extends Controller
{

    public function generarReporte($idProceso, $anio)
    {

        try {
            $proceso = Proceso::with(['entidad', 'usuario'])->findOrFail($idProceso);
            $mapa = MapaProceso::where('idProceso', $idProceso)->first();
            $planControlActividades = ActividadControl::where('idProceso', $idProceso)->get();
        } catch (\Exception $e) {
            Log::error("❌ Error cargando información del proceso", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al obtener información del proceso'], 500);
        }


        // ✅ Gestión de Riesgos
        $registroRiesgo = Registros::where('idProceso', $idProceso)->first();
        $mapa = MapaProceso::where('idProceso', $idProceso)->first();
        $actividades = ActividadControl::where('idProceso', $idProceso)->get();
        $auditorias = Auditoria::where('idProceso', $idProceso)->get();

        $registro = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Gestión de Riesgo')
            ->first();

        $gestion = $registroRiesgo ? GestionRiesgos::where('idRegistro', $registroRiesgo->idRegistro)->first() : null;
        $riesgos = $gestion ? Riesgo::where('idGesRies', $gestion->idGesRies)->get() : collect();

        // ✅ Plan de Control: Indicadores + interpretación y necesidad
        $registroIndicadores = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Análisis de Datos')
            ->first();

        $planControlIndicadores = [];
        $interpretacion = null;
        $necesidad = null;

        if ($registroIndicadores) {
            $indicadores = IndicadorConsolidado::where('idProceso', $idProceso)
                ->where('origenIndicador', 'ActividadControl')
                ->get();

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
                ->where('seccion', 'Conformidad')
                ->first();

            if ($analisis) {
                $interpretacion = $analisis->interpretacion;
                $necesidad = $analisis->necesidad;
            }
        }

        // ✅ Rutas de las gráficas
        if (!$registro) {
            return response()->json(['error' => 'No se encontró el registro.'], 404);
        }



        $gestion = GestionRiesgos::where('idRegistro', $registro->idRegistro)->first();
        if (!$gestion) {
            return response()->json(['error' => 'No se encontró gestión de riesgos.'], 404);
        }
        $riesgos = Riesgo::where('idGesRies', $gestion->idGesRies)->get();
        $graficaPlanControl = public_path("storage/graficas/plan_control_{$idProceso}_{$anio}.png");
        $graficaEncuesta = public_path("storage/graficas/encuesta_{$idProceso}_{$anio}.png");
        $graficaRetroalimentacion = public_path("storage/graficas/retroalimentacion_{$idProceso}_{$anio}.png");
        $graficaMP = public_path("storage/graficas/mapaProceso_{$idProceso}_{$anio}.png");
        $graficaRiesgos = public_path("storage/graficas/riesgos_{$idProceso}_{$anio}.png");
        $graficaEvaluacion = public_path("storage/graficas/evaluacionProveedores_{$idProceso}_{$anio}.png");


        /* Segumientos */
        $registroSeg = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Seguimiento')
            ->first();

        if (!$registroSeg) {
            return response()->json(['error' => 'No se encontró el registro.'], 404);
        }

        $seguimientos = SeguimientoMinuta::where('idRegistro', $registroSeg->idRegistro)->get();
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
        $recursos = Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
        $actividadesPM = ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();

        //Segunda tabla de Analisis (Satisfacción)
        // Obtener indicadores de satisfacción para mostrar en tabla Blade
        $indicadoresJson = $this->indicadoresSatisfaccionCliente($idProceso, $anio)->getContent();
        $indicadoresSatisfaccion = json_decode($indicadoresJson, true);

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

            // ✅ Nuevas claves para el Blade
            'planControlActividades' => $planControlActividades,
            'planControlIndicadores' => $planControlIndicadores,
            'interpretacionPlanControl' => $interpretacion,
            'necesidadPlanControl' => $necesidad,

            'planControl' => $actividades,
            'auditorias' => $auditorias,
            'riesgos' => $riesgos,
            'graficaPlanControl' => $graficaPlanControl,
            'graficaEncuesta' => $graficaEncuesta,
            'graficaRetroalimentacion' => $graficaRetroalimentacion,
            'graficaMP' => $graficaMP,
            'graficaRiesgos' => $graficaRiesgos,
            'graficaEvaluacion' => $graficaEvaluacion,
            'registro' => $registro->idRegistro,
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
            'indicadoresSatisfaccion' => $indicadoresSatisfaccion,

        ];

        try {
            Log::info("📄 Generando PDF con datos enviados a la vista.");
            $pdf = Pdf::loadView('proceso', $datos);
            return $pdf->download("reporte_proceso_{$anio}.pdf");
        } catch (\Exception $e) {
            Log::error("❌ Error al generar PDF", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }




    public function obtenerDatosReporte($idProceso, $anio)
    {
        try {
            // Obtener el proceso con la entidad y usuario líder
            $proceso = Proceso::with(['entidad', 'usuario'])->where('idProceso', $idProceso)->firstOrFail();
            // $mapaProceso = MapaProceso::where('idProceso', $idProceso)->get();
            return response()->json([
                'nombreProceso' => $proceso->nombreProceso,
                'entidad' => $proceso->entidad->nombreEntidad ?? 'Entidad no disponible',
                'liderProceso' => $proceso->usuario->nombre ?? 'Líder no asignado',
                'objetivo' => $proceso->objetivo ?? 'No especificado',
                'alcance' => $proceso->alcance ?? 'No especificado',
                'norma' => $proceso->norma ?? 'No especificado',
                'anioCertificacion' => $proceso->anioCertificado ?? 'No especificado',
                'estado' => $proceso->estado ?? 'No especificado',
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
}
