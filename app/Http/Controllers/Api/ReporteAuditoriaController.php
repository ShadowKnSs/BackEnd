<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReporteAuditoria;
use App\Models\AuditoriaInterna;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteAuditoriaController extends Controller
{
    public function index()
    {
        return ReporteAuditoria::with('auditoria')->get();
    }

    public function store(Request $request)
    {
        $reporte = ReporteAuditoria::create($request->all());
        return response()->json($reporte, 201);
    }

    public function destroy($id)
    {
        $reporte = ReporteAuditoria::findOrFail($id);
        $reporte->delete();
        return response()->json(['message' => 'Reporte eliminado correctamente']);
    }

    public function descargarPDF($id)
    {
        $auditoria = AuditoriaInterna::with([
            'criterios',
            'equipoAuditor',
            'personalAuditado',
            'verificacionRuta',
            'puntosMejora',
            'conclusiones',
            'plazos'
        ])->findOrFail($id);        
    
        $pdf = Pdf::loadView('pdf.reporteAud', compact('auditoria'));
        return $pdf->download('informe_auditoria_' . $id . '.pdf');
    }
}
