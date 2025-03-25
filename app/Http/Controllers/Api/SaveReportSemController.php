<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReporteSemestral;

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

    public function obtenerReportesSemestrales()
{
    $reportes = ReporteSemestral::select('idReporteSemestral', 'anio', 'periodo', 'fechaGeneracion', 'ubicacion')->get();
    return response()->json($reportes);
}
}
