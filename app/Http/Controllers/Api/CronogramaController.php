<?php

namespace App\Http\Controllers\Api;

use App\Models\Cronograma;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Notifications\AuditoriaNotificacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CronogramaController extends Controller
{

    public function index()
    {
        $auditorias = Cronograma::all();
        return response()->json($auditorias);
    }

    public function store(Request $request)
{
    Log::info('Iniciando el método store');

    $request->validate([
        'fechaProgramada' => 'required|date',
        'horaProgramada' => 'required',
        'tipoAuditoria' => 'required|in:interna,externa',
        'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
        'descripcion' => 'required',
        'nombreProceso' => 'required|string',
        'nombreEntidad' => 'required|string',
        'auditorLider' => 'nullable|integer'
    ]);

    Log::info('Validación de datos completada', ['request_data' => $request->all()]);

    $proceso = Proceso::where('nombreProceso', $request->nombreProceso)->first();
    if (!$proceso) {
        Log::warning('Proceso no encontrado', ['nombreProceso' => $request->nombreProceso]);
        return response()->json([
            'message' => 'El proceso no existe'
        ], 404);
    }

    $idProceso = $proceso->idProceso;
    $idUsuario = $proceso->idUsuario;
    Log::info('Proceso encontrado', ['idProceso' => $idProceso, 'idUsuario' => $idUsuario]);

    $auditoriaData = $request->only([
        'fechaProgramada', 'horaProgramada', 'tipoAuditoria', 'estado', 'descripcion', 'nombreProceso', 'nombreEntidad', 'auditorLider'
    ]);
    $auditoriaData['idProceso'] = $idProceso;
    
    $auditoria = Cronograma::create($auditoriaData);
    Log::info('Auditoría creada', ['auditoria' => $auditoria]);

    $usersList = [];
    $emails = [];

    if ($request->auditorLider) {
        $auditorLider = Usuario::where('idUsuario', $request->auditorLider)->first();
        if ($auditorLider) {
            $usersList[] = $auditorLider->nombre . ' ' . $auditorLider->apellidoPat . ' ' . $auditorLider->apellidoMat;
            $emails[] = $auditorLider->correo;
            Log::info('Auditor líder encontrado', ['auditorLider' => $auditorLider->correo]);
        } else {
            Log::warning('Auditor líder no encontrado', ['idUsuario' => $request->auditorLider]);
        }
    }

    $usuarioProceso = Usuario::where('idUsuario', $idUsuario)->first();
    if ($usuarioProceso) {
        $usersList[] = $usuarioProceso->nombre . ' ' . $usuarioProceso->apellidoPat . ' ' . $usuarioProceso->apellidoMat;
        $emails[] = $usuarioProceso->correo;
        Log::info('Usuario responsable del proceso encontrado', ['usuario' => $usuarioProceso->correo]);
    } else {
        Log::warning('Usuario responsable del proceso no encontrado', ['idUsuario' => $idUsuario]);
    }

    Log::info('Correos a notificar', ['emails' => $emails]);

    $cronogramaData = [
        'tipoAuditoria' => $request->tipoAuditoria,
        'fechaProgramada' => $request->fechaProgramada,
        'horaProgramada' => $request->horaProgramada,
        'nombreProceso' => $request->nombreProceso,
        'nombreEntidad' => $request->nombreEntidad
    ];

    foreach ($emails as $email) {
        try {
            Notification::route('mail', $email)->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails));
            Log::info('Notificación enviada', ['email' => $email]);
        } catch (\Exception $e) {
            Log::error('Error al enviar la notificación', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }

    Log::info('Finalizando el método store');

    return response()->json([
        'message' => 'Auditoría guardada en el cronograma y notificación enviada',
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
        'nombreEntidad' => 'required|string',
        'auditorLider' => 'nullable|string'
    ]);

    $auditoria = Cronograma::findOrFail($id);
    $auditoria->update($request->all());

    return response()->json([
        'message' => 'Auditoría actualizada con éxito',
        'auditoria' => $auditoria
    ], 200);
}

}

