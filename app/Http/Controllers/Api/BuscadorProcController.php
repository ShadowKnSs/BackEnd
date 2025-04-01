<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuscadorProc;
use App\Models\Proceso;
use Carbon\Carbon;

class BuscadorProcController extends Controller
{
    public function buscarPorAnio(Request $request)
    {
        $anio = $request->query('anio');
        
        if (!$anio || !is_numeric($anio)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingrese un año válido (4 dígitos)'
            ], 400);
        }

        $procesos = Proceso::all(['idProceso', 'nombreProceso as nombreProceso']);

        $reportes = BuscadorProc::with('proceso') 
            ->whereYear('fechaElaboracion', $anio)
            ->orderBy('fechaElaboracion', 'desc')
            ->get([
                'idReporteProceso as id',
                'idProceso',
                'nombreReporte as nombre',
                'fechaElaboracion',
            ]);

        $reportes->transform(function ($reporte) {
            return [
                'id' => $reporte->id,
                'idProceso' => $reporte->idProceso,
                'nombreProceso' => $reporte->proceso->nombre ?? 'Proceso no encontrado',
                'nombre' => $reporte->nombre,
                'fecha' => Carbon::parse($reporte->fechaElaboracion)->format('d/m/Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'anio' => $anio,
            'procesos' => $procesos,
            'total' => $reportes->count(),
            'data' => $reportes
        ]);
    }
}
