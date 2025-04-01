<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReporteSemestralController extends Controller
{
    public function generarPDF(Request $request)
{
    // Validar que las variables obligatorias estén presentes
    $request->validate([
        'anio' => 'required',
        'periodo' => 'required',
        'conclusion' => 'required'
    ]);

    // Obtener los datos del request
    $imagenes = $request->input('imagenes', []);
    $datosRiesgos = $request->input('listas.datosRiesgos', []);
    $datosIndicadores = $request->input('listas.datosIndicadores', []);
    $datosAccionesMejora = $request->input('listas.datosAccionesMejora', []);
    $datosAuditorias = $request->input('listas.datosAuditorias', []);
    $datosSeguimiento = $request->input('listas.datosSeguimiento', []);
    $fortalezas = $request->input('fortalezas', []);
    $debilidades = $request->input('debilidades', []);
    $conclusion = $request->input('conclusion');
    $anio = $request->input('anio');
    $periodo = $request->input('periodo');

    // Generar el PDF con los datos recibidos
    $pdf = PDF::loadView('pdf.reporte', compact(
        'imagenes',
        'datosRiesgos',
        'datosIndicadores',
        'datosAccionesMejora',
        'datosAuditorias',
        'datosSeguimiento',
        'fortalezas',
        'debilidades',
        'conclusion',
        'anio',
        'periodo'
    ));

    // Descargar el archivo PDF
    return $pdf->download('reporte_semestral.pdf');
}

}
