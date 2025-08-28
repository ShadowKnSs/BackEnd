<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuscadorProc;
use App\Models\Proceso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class BuscadorProcController extends Controller
{
    public function buscarPorAnio(Request $request)
    {
        $anio = $request->query('anio');
        $lider = $request->query('lider');

        if (!$anio || !is_numeric($anio)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingrese un año válido (4 dígitos)'
            ], 400);
        }

        // Procesos
        $procesos = DB::table('proceso')
            ->join('entidaddependencia', 'proceso.idEntidad', '=', 'entidaddependencia.idEntidadDependencia')
            ->select(
                'proceso.idProceso',
                DB::raw("CONCAT(entidaddependencia.nombreEntidad, ' - ', proceso.nombreProceso) AS nombreCompleto")
            )
            ->get();

        // Reportes
        $query = BuscadorProc::with(['proceso.usuario.roles'])
            ->whereYear('fechaElaboracion', $anio);

        if ($lider) {
            $query->whereHas('proceso.usuario', function ($q) use ($lider) {
                $q->where('idUsuario', $lider);
            });
        }

        $reportes = $query->orderBy('fechaElaboracion', 'desc')
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
                'nombreProceso' => $reporte->proceso->nombreProceso ?? 'Proceso no encontrado',
                'liderProceso' => $reporte->proceso->usuario->nombre ?? 'Líder no asignado',
                'nombre' => $reporte->nombre,
                'fecha' => Carbon::parse($reporte->fechaElaboracion)->toDateString(),
            ];
        });

        // Líderes
        $leaders = Usuario::with(['roles'])
            ->whereHas('roles', function ($q) {
                $q->where('nombreRol', 'Líder');
            })
            ->get([
                'idUsuario',
                DB::raw("CONCAT(nombre, ' ', apellidoPat, ' ', apellidoMat) as nombreCompleto")
            ]);

        return response()->json([
            'success' => true,
            'anio' => $anio,
            'procesos' => $procesos,
             'leaders' => $leaders,
            'total' => $reportes->count(),
            'data' => $reportes
        ]);
    }
}