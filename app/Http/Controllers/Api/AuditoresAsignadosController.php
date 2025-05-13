<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditoresAsignados;
use App\Models\Cronograma;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class AuditoresAsignadosController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'idAuditoria' => 'required|exists:auditorias,idAuditoria',
            'auditores' => 'required|array',
            'auditores.*' => 'integer|exists:usuario,idUsuario'
        ]);

        try {
            DB::beginTransaction();
            
            $idAuditoria = $request->idAuditoria;
            $auditores = $request->auditores;
            
            AuditoresAsignados::where('idAuditoria', $idAuditoria)->delete();
            
            foreach ($auditores as $idAuditor) {
                $auditor = Usuario::findOrFail($idAuditor);
                
                AuditoresAsignados::create([
                    'idAuditoria' => $idAuditoria,
                    'idAuditor' => $idAuditor,
                    'nombreAuditor' => $auditor->nombre . ' ' . $auditor->apellidoPat,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Auditores asignados correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar auditores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($idAuditoria)
    {
        $auditores = AuditoresAsignados::where('idAuditoria', $idAuditoria)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->idAuditor,
                    'nombre' => $item->nombreAuditor
                ];
            });
            
        return response()->json($auditores);
    }
}