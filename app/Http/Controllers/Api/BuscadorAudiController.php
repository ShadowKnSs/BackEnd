<?php

namespace App\Http\Controllers\Api;

use App\Models\BuscadorAudi; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class BuscadorAudiController extends Controller
{
    public function buscarPorAnio(Request $request)
    {
        $anio = $request->query('fechaGeneracion');
        $auditor = $request->query('auditor');
        $idEntidad = $request->query('idEntidad');
        $idProceso = $request->query('idProceso');

        if (!$anio || !is_numeric($anio)) {
            return response()->json(['error' => 'Por favor ingrese un año válido.'], 400);
        }

        $query = BuscadorAudi::query()
            ->whereYear('reportesauditoria.fechaGeneracion', $anio)
            ->join('auditoriainterna as ai', 'ai.idAuditorialInterna', '=', 'reportesauditoria.idAuditorialInterna')
            ->join('Registros as r', 'r.idRegistro', '=', 'ai.idRegistro')
            ->join('proceso as p', 'p.idProceso', '=', 'r.idProceso')
            ->join('entidaddependencia as ed', 'ed.idEntidadDependencia', '=', 'p.idEntidad');

        if ($auditor) {
            $query->where('ai.auditorLider', 'like', "%$auditor%");
        }

        if ($idEntidad) {
            $query->where('p.idEntidad', $idEntidad);
        }

        if ($idProceso) {
            $query->where('p.idProceso', $idProceso);
        }

        $auditorias = $query->get([
            'reportesauditoria.idReporte',
            'reportesauditoria.idAuditorialInterna',
            'reportesauditoria.fechaGeneracion',
            'reportesauditoria.ruta',
            'ai.auditorLider',
            'ed.nombreEntidad as entidad',
            'p.nombreProceso as proceso'
        ]);

        $resultados = $auditorias->map(function ($auditoria) {
            return [
                'idReporte' => $auditoria->idReporte,
                'idAuditorialInterna' => $auditoria->idAuditorialInterna,
                'fechaGeneracion' => Carbon::parse($auditoria->fechaGeneracion)->format('d/m/Y'),
                'ruta' => $auditoria->ruta,
                'auditorLider' => $auditoria->auditorLider,
                'entidad' => $auditoria->entidad ?? 'Sin entidad',
                'proceso' => $auditoria->proceso ?? 'Sin proceso'
            ];
        });

        return response()->json($resultados->values());
    }
}
