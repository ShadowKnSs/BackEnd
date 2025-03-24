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
        Log::info("🔹 Iniciando generación de reporte", ['idProceso' => $idProceso, 'anio' => $anio]);

        // Obtener el proceso con su entidad asociada
        try {
            $proceso = Proceso::with(['entidad', 'usuario'])->where('idProceso', $idProceso)->firstOrFail();
            Log::info("✅ Proceso encontrado", ['proceso' => $proceso->nombreProceso, 'entidad' => $proceso->entidad->nombreEntidad]);
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener el proceso", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }

        // Verificar si hay registros del proceso en ese año
        try {
            $registro = Registros::where('idProceso', $idProceso)->where('año', $anio)->first();
            if (!$registro) {
                Log::warning("⚠️ No hay registros para este año", ['idProceso' => $idProceso, 'anio' => $anio]);
                return response()->json(['error' => 'No hay registros para este año'], 404);
            }
            Log::info("✅ Registro encontrado", ['idProceso' => $idProceso, 'anio' => $anio]);
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener el registro", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al obtener el registro'], 500);
        }
        

        $mapa = MapaProceso::where('idProceso', $idProceso)->first();
        $actividades = ActividadControl::where('idProceso', $idProceso)->get();
        $auditorias = Auditoria::where('idProceso', $idProceso)->get();

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
        $actividadesSeg= ActividadMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();
        $compromisosSeg= CompromisoMinuta::whereIn('idSeguimiento', $idSeguimientos)->get();

        $registroAcMejora = Registros::where('idProceso', $idProceso)
            ->where('año', $anio)
            ->where('apartado', 'Acciones de Mejora')
            ->first();

        if (!$registroAcMejora) {
            return response()->json(['error' => 'No se encontró el registro.'], 404);
        }
        $acMejora = ActividadMejora::where('idRegistro', $registroAcMejora->idRegistro)->get();
        $idAccMejora = $acMejora->pluck('idActividadMejora')->toArray();
        $proyectoMejora= ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();
        $recursos=Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
        $actividadesPM=ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
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
            'planControl' => $actividades,
            'auditorias' => $auditorias,
            'riesgos' => $riesgos,
            'graficaPlanControl' => $graficaPlanControl,
            'graficaEncuesta' =>  $graficaEncuesta,
            'graficaRetroalimentacion' => $graficaRetroalimentacion,
            'graficaMP' => $graficaMP,
            'graficaRiesgos' => $graficaRiesgos,
            'graficaEvaluacion' => $graficaEvaluacion,
            'registro' => $registro->idRegistro,
            'seguimientos'=> $seguimientos,
            'idseguimientos'=> $idSeguimientos,
            'asistentes'=> $asistentes,
            'actividadesSeg'=> $actividadesSeg,
            'compromisosSeg'=>$compromisosSeg,
            'Accion Mejora'=>$acMejora,
            'idAcciones'=> $idAccMejora,
            'proyectoMejora'=>$proyectoMejora,
            'recursos'=> $recursos,
            'actividadesPM'=> $actividadesPM
        ];

        Log::info("📄 Datos enviados a la vista", $datos);

        try {
            // Generar el PDF
            Log::info("📄 Generando PDF");
            $pdf = Pdf::loadView('proceso', $datos);
            Log::info("✅ PDF generado con éxito");


            return $pdf->download("reporte_proceso_{$anio}.pdf");
        } catch (\Exception $e) {
            Log::error("❌ Error al generar el PDF", ['error' => $e->getMessage()]);
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
            $proyectoMejora= ProyectoMejora::whereIn('idActividadMejora', $idAccMejora)->first();
            $recursos=Recurso::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            $actividadesPM=ActividadesPM::where('idProyectoMejora', $proyectoMejora->idProyectoMejora)->get();
            return response()->json([
                'acMejora'=> $acMejora,
                'proyectoMejora' => $proyectoMejora,
                'recursos' => $recursos,
                'actividadesPM' => $actividadesPM,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener', 'detalle' => $e->getMessage()], 500);
        }
    }
}
