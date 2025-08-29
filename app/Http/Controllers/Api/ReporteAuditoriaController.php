<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReporteAuditoria;
use App\Models\AuditoriaInterna;
use App\Models\BuscadorAudi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteAuditoriaController extends Controller
{
    public function index()
    {
        return ReporteAuditoria::with([
            'auditoria.registro.proceso.entidad'
        ])->get();
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
            'plazos',
            'registro.proceso.entidad'
        ])->findOrFail($id);        

        $pdf = Pdf::loadView('pdf.reporteAud', compact('auditoria'));
        return $pdf->download('informe_auditoria_' . $id . '.pdf');
    }

    public function buscar(Request $request)
    {
        $query = ReporteAuditoria::query();
        
        // Mantener la compatibilidad con búsquedas existentes
        if ($request->has('texto')) {
            $query->where('hallazgo', 'like', '%'.$request->texto.'%');
        }
        
        // Nuevos filtros para el buscador
        if ($request->has('filtros')) {
            $this->aplicarFiltrosAvanzados($query, $request->filtros);
        }
        
        $reportes = $query->orderBy('fechaGeneracion', 'desc')
                         ->paginate(10);
        
        return response()->json($reportes);
    }
    
    protected function aplicarFiltrosAvanzados($query, $filtros)
    {
        if (isset($filtros['fechaInicio'])) {
            $query->where('fechaGeneracion', '>=', $filtros['fechaInicio']);
        }
        
        if (isset($filtros['fechaFin'])) {
            $query->where('fechaGeneracion', '<=', $filtros['fechaFin']);
        }
        
        if (isset($filtros['idProceso'])) {
            $query->whereHas('auditoriaInterna.registro', function($q) use ($filtros) {
                $q->where('idProceso', $filtros['idProceso']);
            });
        }
        
        if (isset($filtros['idEntidad'])) {
            $query->whereHas('auditoriaInterna.registro.proceso', function($q) use ($filtros) {
                $q->where('idEntidad', $filtros['idEntidad']);
            });
        }
        
        if (isset($filtros['idMacroproceso'])) {
            $query->whereHas('auditoriaInterna.registro.proceso.macroproceso', function($q) use ($filtros) {
                $q->where('idMacroproceso', $filtros['idMacroproceso']);
            });
        }
    }

    public function buscar1(Request $request)
    {
        $filtros = $request->validate([
            'fechaGeneracionInicio' => 'nullable|date',
            'fechaGeneracionFin' => 'nullable|date|after_or_equal:fechaGeneracionInicio',
            'idProceso' => 'nullable|integer',
            'idEntidad' => 'nullable|integer',
            'idMacroproceso' => 'nullable|integer'
        ]);

        $reportes = BuscadorAudi::filtrar($filtros)
            ->orderBy('fechaGeneracion', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $reportes,
            'filtros' => $filtros
        ]);
    }

    public function descargar($id)
    {
        $reporte = BuscadorAudi::findOrFail($id);
        
        // Aquí implementarías la lógica para generar el PDF/Excel
        // Por ahora solo redirige a la ruta si existe
        if($reporte->ruta) {
            return response()->download(storage_path('app/' . $reporte->ruta));
        }
        
        abort(404, 'Archivo no encontrado');
    }
}
