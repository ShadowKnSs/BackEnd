<?php

namespace App\Http\Controllers\Api;


use App\Models\MapaProceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Proceso;
use App\Models\Registros;
use App\Models\ActividadControl;
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


}
