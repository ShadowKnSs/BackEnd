<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProyectoMejora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ReporteProyectoMejoraController extends Controller
{
    public function generar($idProyecto)
    {
        $proyecto = ProyectoMejora::with([
            'objetivos',
            'responsablesInv',
            'indicadoresExito',
            'recursos',
            'actividades'
        ])->find($idProyecto);

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $pdf = Pdf::loadView('pdf.proyectoMejora', compact('proyecto'))->setPaper('A4', 'portrait');
        return $pdf->download("Reporte_ProyectoMejora_{$idProyecto}.pdf");
    }
}
