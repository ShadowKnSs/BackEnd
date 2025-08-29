<?php

namespace App\Http\Controllers\Api;

use App\Models\BuscadorAudi; 
use App\Models\Proceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;


class BuscadorAudiController extends Controller
{
    public function buscarPorAnio(Request $request)
{
    $anio = $request->query('fechaGeneracion');
    $auditor = $request->query('auditor');

    if (!$anio || !is_numeric($anio)) {
        return response()->json(['error' => 'Por favor ingrese un año válido.'], 400);
    }

    $query = BuscadorAudi::query()
        ->whereYear('fechaGeneracion', $anio)
        ->join('auditoriainterna as ai', 'ai.idAuditorialInterna', '=', 'reportesauditoria.idAuditorialInterna');

    if ($auditor) {
        $query->where('ai.auditorLider', 'like', "%$auditor%");
    }

    $auditorias = $query->get([
        'reportesauditoria.idReporte',
        'reportesauditoria.idAuditorialInterna',
        'reportesauditoria.fechaGeneracion',
        'reportesauditoria.ruta',
        'ai.auditorLider'
    ]);

    $resultados = $auditorias->map(function ($auditoria) {
        return [
            'idReporte' => $auditoria->idReporte,
            'idAuditorialInterna' => $auditoria->idAuditorialInterna,
            'fechaGeneracion' => Carbon::parse($auditoria->fechaGeneracion)->format('d/m/Y'),
            'ruta' => $auditoria->ruta,
            'auditorLider' => $auditoria->auditorLider
        ];
    });

    return response()->json($resultados->values());
}

}
