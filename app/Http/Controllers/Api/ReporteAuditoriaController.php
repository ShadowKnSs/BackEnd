<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReporteAuditoria;
use Illuminate\Http\Request;

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
}
