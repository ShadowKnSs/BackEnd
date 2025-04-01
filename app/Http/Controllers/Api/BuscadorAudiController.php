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
        if (
            !$anio || 
            !is_numeric($anio)
        ) {
            return response()->json(['error' => 'Por favor ingrese un año válido.'], 400);
        }

        $query = BuscadorAudi::whereYear('fechaGeneracion', $anio);

        $auditorias = $query->get(['idReporte', 'fechaGeneracion', 'hallazgo', 'oportunidadesMejora', 'ruta']);

        $resultados = $auditorias->map(function ($auditoria) {
            return [
                'idReporte' => $auditoria->idReporte,
                'fechaGeneracion' => Carbon::parse($auditoria->fechaGeneracion)->format('Y-m-d'),
                'hallazgo' => $auditoria->hallazgo ?? "",
                'oportunidadesMejora' => $auditoria->oportunidadesMejora ?? "",
                'ruta' => $auditoria->ruta,
                'tieneHallazgos' => !empty($auditoria->hallazgo) && trim($auditoria->hallazgo) !== "" && trim($auditoria->hallazgo) !== "Sin hallazgos",
                'tieneOportunidades' => !empty($auditoria->oportunidadesMejora) && trim($auditoria->oportunidadesMejora) !== "" && trim($auditoria->oportunidadesMejora) !== "Sin oportunidades"
            ];
        });

        if ($request->has('con_contenido')) {
            $resultados = $resultados->filter(function ($auditoria) {
                return $auditoria['tieneHallazgos'] || $auditoria['tieneOportunidades'];
            });
        }

        return response()->json($resultados->values());
    }
}
