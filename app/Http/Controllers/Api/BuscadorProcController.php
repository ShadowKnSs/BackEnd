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
        $proceso = $request->query('proceso');

        // Procesos para el dropdown
        $procesos = DB::table('proceso')
            ->join('entidaddependencia', 'proceso.idEntidad', '=', 'entidaddependencia.idEntidadDependencia')
            ->select(
                'proceso.idProceso',
                DB::raw("CONCAT(entidaddependencia.nombreEntidad, ' - ', proceso.nombreProceso) AS nombreCompleto")
            )
            ->get();

        // Reportes - Consulta base
        $query = BuscadorProc::with(['proceso.usuario.roles', 'proceso.entidad']);

        // Filtro por año (si se proporciona)
        if ($anio && is_numeric($anio)) {
            $query->whereYear('fechaElaboracion', $anio);
        }

        // Filtro por líder (si se proporciona)
        if ($lider) {
            $query->whereHas('proceso.usuario', function ($q) use ($lider) {
                $q->where('idUsuario', $lider);
            });
        }

        // Filtro por proceso (si se proporciona) - NUEVO FILTRO
        if ($proceso) {
            $query->where('idProceso', $proceso);
        }

        $reportes = $query->orderBy('fechaElaboracion', 'desc')
            ->get([
                'idReporteProceso as id',
                'idProceso',
                'nombreReporte as nombre',
                'fechaElaboracion',
                'ruta',
            ]);

        $reportes->transform(function ($reporte) {
            return [
                'id' => $reporte->id,
                'idProceso' => $reporte->idProceso,
                'nombreProceso' => $reporte->proceso->nombreProceso ?? 'Proceso no encontrado',
                'nombreEntidad' => $reporte->proceso->entidad->nombreEntidad ?? 'Entidad no encontrada',
                'liderProceso' => $reporte->proceso->usuario->nombre ?? 'Líder no asignado',
                'nombre' => $reporte->nombre,
                'fecha' => Carbon::parse($reporte->fechaElaboracion)->toDateString(),
                'ruta' => $reporte->ruta,
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
            'lider' => $lider,
            'proceso' => $proceso,
            'procesos' => $procesos,
            'leaders' => $leaders,
            'total' => $reportes->count(),
            'data' => $reportes
        ]);
    }
}