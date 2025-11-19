<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuscadorProc;
use App\Models\Proceso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

/**
 * @class BuscadorProcController
 * @description Controlador API que maneja la lógica de búsqueda, filtrado y recuperación de reportes de proceso, incluyendo las listas de dropdown necesarias.
 */
class BuscadorProcController extends Controller
{
    /**
     * @method buscarPorAnio
     * @description GET /api/buscador/buscar-por-anio - Aplica filtros por año, líder y proceso a la lista de reportes.
     * @param \Illuminate\Http\Request $request - Contiene los parámetros 'anio', 'lider' y 'proceso' en la query.
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarPorAnio(Request $request)
    {
        // Parámetros de consulta
        $anio = $request->query('anio');
        $lider = $request->query('lider');
        $proceso = $request->query('proceso');

        // 1. Obtener lista de Procesos para el dropdown de filtros
        $procesos = DB::table('proceso')
            ->join('entidaddependencia', 'proceso.idEntidad', '=', 'entidaddependencia.idEntidadDependencia')
            ->select(
                'proceso.idProceso',
                DB::raw("CONCAT(entidaddependencia.nombreEntidad, ' - ', proceso.nombreProceso) AS nombreCompleto")
            )
            ->get();

        // 2. Consulta de Reportes (BuscadorProc) con eager loading
        $query = BuscadorProc::with(['proceso.usuario.roles', 'proceso.entidad']);

        // Filtro por año
        if ($anio && is_numeric($anio)) {
            $query->whereYear('fechaElaboracion', $anio);
        }

        // Filtro por líder (busca en la relación proceso.usuario)
        if ($lider) {
            $query->whereHas('proceso.usuario', function ($q) use ($lider) {
                $q->where('idUsuario', $lider);
            });
        }

        // Filtro por proceso (idProceso)
        if ($proceso) {
            $query->where('idProceso', $proceso);
        }

        // Ejecutar consulta y seleccionar campos
        $reportes = $query->orderBy('fechaElaboracion', 'desc')
            ->get([
                'idReporteProceso as id',
                'idProceso',
                'nombreReporte as nombre',
                'fechaElaboracion',
                'ruta',
            ]);

        // 3. Transformar resultados para aplanar las relaciones y formatear la fecha
        $reportes->transform(function ($reporte) {
            // Se asume que las relaciones 'proceso', 'proceso.entidad' y 'proceso.usuario' fueron cargadas (eager loaded)
            return [
                'id' => $reporte->id,
                'idProceso' => $reporte->idProceso,
                'nombreProceso' => $reporte->proceso->nombreProceso ?? 'Proceso no encontrado',
                'nombreEntidad' => $reporte->proceso->entidad->nombreEntidad ?? 'Entidad no encontrada',
                // Se asume que el usuario es el Líder del proceso
                'liderProceso' => $reporte->proceso->usuario->nombre ?? 'Líder no asignado', 
                'nombre' => $reporte->nombre,
                'fecha' => Carbon::parse($reporte->fechaElaboracion)->toDateString(),
                'ruta' => $reporte->ruta,
            ];
        });

        // 4. Obtener la lista de Líderes (Usuarios con rol 'Líder')
        $leaders = Usuario::with(['roles'])
            ->whereHas('roles', function ($q) {
                $q->where('nombreRol', 'Líder');
            })
            ->get([
                'idUsuario',
                DB::raw("CONCAT(nombre, ' ', apellidoPat, ' ', apellidoMat) as nombreCompleto")
            ]);

        // 5. Devolver la respuesta consolidada con datos y listas de filtros
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