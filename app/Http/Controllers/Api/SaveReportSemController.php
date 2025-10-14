<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReporteSemestral;
use Illuminate\Support\Facades\Log;


class SaveReportSemController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'anio' => 'required|integer',
            'periodo' => 'required|string|max:50',
            'fortalezas' => 'nullable|string',
            'debilidades' => 'nullable|string',
            'conclusion' => 'required|string',
            'fechaGeneracion' => 'required|date_format:Y-m-d H:i:s',
            'ubicacion' => 'required|string'
        ]);

        // Verificar si ya existe un reporte con el mismo año y periodo
        $existeReporte = ReporteSemestral::where('anio', $request->anio)
            ->where('periodo', $request->periodo)
            ->exists();

        if ($existeReporte) {
            return response()->json(['error' => 'Ya existe un reporte para este año y periodo'], 409);
        }

        // Crear un nuevo registro
        $reporte = ReporteSemestral::create([
            'anio' => $request->anio,
            'periodo' => $request->periodo,
            'fortalezas' => $request->fortalezas,
            'debilidades' => $request->debilidades,
            'conclusion' => $request->conclusion,
            'fechaGeneracion' => $request->fechaGeneracion,
            'ubicacion' => $request->ubicacion
        ]);

        return response()->json(['message' => 'Reporte semestral registrado con éxito', 'reporte' => $reporte], 201);
    }
    public function verificarReporteExistente(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'periodo' => 'required|string|max:50',
        ]);

        $existeReporte = ReporteSemestral::where('anio', $request->anio)
            ->where('periodo', $request->periodo)
            ->exists();

        if ($existeReporte) {
            return response()->json(['exists' => true, 'message' => 'Ya existe un reporte para este año y periodo'], 200);
        }

        return response()->json(['exists' => false, 'message' => 'No existe un reporte para este año y periodo'], 200);
    }

    /*public function obtenerReportesSemestrales()
    {
        $reportes = ReporteSemestral::select('idReporteSemestral', 'anio', 'periodo', 'fechaGeneracion', 'ubicacion')->get();

        if ($reportes->isEmpty()) {
            return response()->json(['message' => 'No hay reportes semestrales disponibles'], 404);
        }

        return response()->json($reportes);
    }*/
    public function obtenerReportesSemestrales()
    {
        Log::info('Iniciando método obtenerReportesSemestrales');

        try {
            $reportes = ReporteSemestral::select('idReporteSemestral', 'anio', 'periodo', 'fechaGeneracion', 'ubicacion')->get();

            Log::info('Consulta ejecutada correctamente', [
                'total_reportes' => $reportes->count()
            ]);

            if ($reportes->isEmpty()) {
                Log::warning('No se encontraron reportes semestrales en la base de datos');
                return response()->json(['message' => 'No hay reportes semestrales disponibles'], 404);
            }

            Log::info('Retornando respuesta con reportes', [
                'primer_reporte' => $reportes->first()
            ]);

            return response()->json($reportes);
        } catch (\Exception $e) {
            Log::error('Error en obtenerReportesSemestrales', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['error' => 'Ocurrió un error al obtener los reportes semestrales'], 500);
        }
    }


    public function eliminarReporteSemestral($id)
    {
        $reporte = ReporteSemestral::find($id);

        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

        // Eliminar el reporte
        $reporte->delete();

        return response()->json(['message' => 'Reporte eliminado con éxito'], 200);
    }
}
