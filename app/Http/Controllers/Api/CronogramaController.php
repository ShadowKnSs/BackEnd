<?php

namespace App\Http\Controllers\Api;

use App\Models\Cronograma;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Proceso;


class CronogramaController extends Controller
{

    public function index()
    {
        $auditorias = Cronograma::all();
        return response()->json($auditorias);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fechaProgramada' => 'required|date',
            'horaProgramada' => 'required',
            'tipoAuditoria' => 'required|in:interna,externa',
            'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
            'descripcion' => 'required',
            'nombreProceso' => 'required|string',
            'nombreEntidad' => 'required|string'
        ]);
        $proceso = Proceso::where('nombreProceso', $request->nombreProceso)->first();
        if (!$proceso) {
            return response()->json([
                'message' => 'El proceso no existe'
            ], 404);
        }
        $idProceso = $proceso->idProceso;
        $auditoria = Cronograma::create([
            'idProceso' => $idProceso,
            'fechaProgramada' => $request->fechaProgramada,
            'horaProgramada' => $request->horaProgramada,
            'tipoAuditoria' => $request->tipoAuditoria,
            'estado' => $request->estado,
            'descripcion' => $request->descripcion,
            'nombreProceso' => $request->nombreProceso,
            'nombreEntidad' => $request->nombreEntidad,
        ]);
        return response()->json([
            'message' => 'Auditoría guardada en el cronograma',
            'auditoria' => $auditoria
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'fechaProgramada' => 'required|date',
            'horaProgramada' => 'required',
            'tipoAuditoria' => 'required|in:interna,externa',
            'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
            'descripcion' => 'required|string',
            'nombreProceso' => 'required|string',
            'nombreEntidad' => 'required|string'
        ]);

        $auditoria = Cronograma::findOrFail($id);
        $auditoria->update($request->all());

        return response()->json(['message' => 'Auditoría actualizada con éxito', 'auditoria' => $auditoria], 200);
    }
}

