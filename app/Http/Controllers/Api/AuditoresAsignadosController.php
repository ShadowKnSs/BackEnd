<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditoresAsignados;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class AuditoresAsignadosController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idAuditoria' => 'required|exists:auditorias,idAuditoria',
            'auditores' => 'required|array',
            'auditores.*' => 'integer|exists:usuario,idUsuario',
        ]);

        $idAuditoria = $validated['idAuditoria'];
        $auditores = $validated['auditores'];

        try {
            DB::transaction(function () use ($idAuditoria, $auditores) {
                // Eliminar antiguos
                AuditoresAsignados::where('idAuditoria', $idAuditoria)->delete();

                // Obtener todos los auditores en una sola consulta
                $usuarios = Usuario::whereIn('idUsuario', $auditores)
                    ->select('idUsuario', 'nombre', 'apellidoPat')
                    ->get()
                    ->keyBy('idUsuario');

                // Insertar todos en batch
                $asignaciones = [];
                foreach ($auditores as $idAuditor) {
                    $usuario = $usuarios[$idAuditor];
                    $asignaciones[] = [
                        'idAuditoria' => $idAuditoria,
                        'idAuditor' => $idAuditor,
                        'nombreAuditor' => $usuario->nombre . ' ' . $usuario->apellidoPat,
                    ];
                }

                AuditoresAsignados::insert($asignaciones);
            });

            return response()->json([
                'success' => true,
                'message' => 'Auditores asignados correctamente'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar auditores: ' . $e->getMessage()
            ], 500);
        }
    }


    public function show($idAuditoria)
    {
        return AuditoresAsignados::where('idAuditoria', $idAuditoria)
            ->select('idAuditor as id', 'nombreAuditor as nombre')
            ->get();
    }

}