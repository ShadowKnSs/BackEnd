<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ReporteSemestralController extends Controller
{
    //
    /*public function generarPDF(Request $request)
    {
        // Recibimos los datos desde React
        $data = $request->all();

        // Renderizamos la vista del PDF con los datos recibidos
        $pdf = Pdf::loadView('pdf.reporte_semestral', compact('data'));

        // Devolver el PDF para descargar
        return $pdf->download('ReporteSemestral.pdf');
    }*/
    public function generarPDF(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'reportData' => 'required|array',
            'reportData.nombreProceso' => 'required|string',
            'reportData.entidad' => 'required|string',
            // Añadir más validaciones para cada campo según lo que recibas
        ]);

        // Obtener los datos que se pasan desde el frontend
        $data = $request->input('reportData');

        // Generar el PDF con los datos
        $pdf = Pdf::loadView('reportes.reporte_semestral', compact('data'));

        // Retornar el PDF como una respuesta
        return $pdf->download('reporte_semestral.pdf');
    }
}
