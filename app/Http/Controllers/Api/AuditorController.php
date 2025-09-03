<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaInterna;
use Illuminate\Http\Request;

class AuditorController extends Controller
{
    public function auditorias($id)
    {
        $auditorias = \DB::table('auditoriainterna as a')
            ->leftJoin('Registros as r', 'a.idRegistro', '=', 'r.idRegistro')
            ->leftJoin('proceso as p', 'r.idProceso', '=', 'p.idProceso')
            ->leftJoin('entidaddependencia as e', 'p.idEntidad', '=', 'e.idEntidadDependencia')
            ->select(
                'a.idAuditorialInterna',
                'a.idRegistro',
                'a.idAuditor',
                'a.auditorLider',
                'a.fechaElabora as fechaAuditoria',
                'a.alcanceAud as tipoAuditoria',
                'a.objetivoAud as nombreProceso',
                'e.nombreEntidad'
            )
            ->where('a.idAuditor', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $auditorias
        ]);
    }
}
