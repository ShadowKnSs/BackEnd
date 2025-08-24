<?php

namespace App\Http\Controllers\Api;

use App\Models\Cronograma;
use App\Models\SupervisorProceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Notifications\AuditoriaNotificacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CronogramaController extends Controller
{
    // JRH - 05/09/25 - Filtro para ver solo un tipo de proceso
    public function index(Request $request)
    {
        // ✅ Validación limpia y estricta
        $data = $request->validate([
            'idProceso' => 'required|integer'
        ]);

        $idProceso = $data['idProceso'];

        // ✅ Log simplificado y estructurado
        \Log::info("🔍 Consultando auditorías para idProceso={$idProceso}");

        // ✅ Consulta optimizada (puedes ajustar columnas si lo deseas con ->select(...))
        $auditorias = Cronograma::where('idProceso', $idProceso)->get();

        return response()->json($auditorias, 200);
    }


    public function store(Request $request)
    {
        Log::info('Iniciando el método store');
        Log::info('Datos recibidos en store', $request->all());


        $validated = $request->validate([
            'fechaProgramada' => 'required|date',
            'horaProgramada' => 'required',
            'tipoAuditoria' => 'required|in:interna,externa',
            'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
            'descripcion' => 'required',
            'nombreProceso' => 'required|string',
            'nombreEntidad' => 'required|string',
            'auditorLider' => 'nullable|integer'
        ]);

        $proceso = Proceso::where('nombreProceso', $validated['nombreProceso'])->first();

        if (!$proceso) {
            Log::warning('Proceso no encontrado', ['nombreProceso' => $validated['nombreProceso']]);
            return response()->json(['message' => 'El proceso no existe'], 404);
        }

        $validated['idProceso'] = $proceso->idProceso;

        $auditoria = Cronograma::create($validated);
        Log::info('Auditoría creada', ['id' => $auditoria->id]);

        // Recolección de correos
        $notificados = collect();
        $emails = [];

        if ($validated['auditorLider']) {
            $auditorLider = Usuario::find($validated['auditorLider']);
            if ($auditorLider) {
                $notificados->push($auditorLider);
                $emails[] = $auditorLider->correo;
            } else {
                Log::warning('Auditor líder no encontrado', ['idUsuario' => $validated['auditorLider']]);
            }
        }

        $usuarioProceso = Usuario::find($proceso->idUsuario);
        if ($usuarioProceso) {
            $notificados->push($usuarioProceso);
            $emails[] = $usuarioProceso->correo;
        } else {
            Log::warning('Usuario del proceso no encontrado', ['idUsuario' => $proceso->idUsuario]);
        }

        $cronogramaData = [
            'tipoAuditoria' => $validated['tipoAuditoria'],
            'fechaProgramada' => $validated['fechaProgramada'],
            'horaProgramada' => $validated['horaProgramada'],
            'nombreProceso' => $validated['nombreProceso'],
            'nombreEntidad' => $validated['nombreEntidad']
        ];

        $userNames = $notificados->map(fn($u) => "{$u->nombre} {$u->apellidoPat} {$u->apellidoMat}")->toArray();

        foreach ($emails as $email) {
            try {
                Notification::route('mail', $email)->notify(
                    new AuditoriaNotificacion($cronogramaData, $userNames, $emails, 'creado')
                );
            } catch (\Throwable $e) {
                Log::error("Error al enviar correo a {$email}", ['error' => $e->getMessage()]);
            }
        }

        $notificados->each(function ($user) use ($cronogramaData, $userNames, $emails) {
            try {
                $user->notify(new AuditoriaNotificacion($cronogramaData, $userNames, $emails, 'creado'));
            } catch (\Throwable $e) {
                Log::error("Error al guardar notificación DB para usuario {$user->idUsuario}", ['error' => $e->getMessage()]);
            }
        });

        Log::info('Finalizando método store');

        return response()->json([
            'message' => 'Auditoría guardada y notificaciones enviadas',
            'auditoria' => $auditoria
        ], 201);
    }

    public function update(Request $request, $id)
    {
        Log::info('Iniciando el método update');

        $validated = $request->validate([
            'fechaProgramada' => 'required|date',
            'horaProgramada' => 'required',
            'tipoAuditoria' => 'required|in:interna,externa',
            'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
            'descripcion' => 'required',
            'nombreProceso' => 'required|string',
            'nombreEntidad' => 'required|string',
            'auditorLider' => 'nullable|integer'
        ]);

        $auditoria = Cronograma::findOrFail($id);

        $proceso = Proceso::where('nombreProceso', $validated['nombreProceso'])->first();
        if (!$proceso) {
            return response()->json(['message' => 'El proceso no existe'], 404);
        }

        $auditoria->update([
            'fechaProgramada' => $validated['fechaProgramada'],
            'horaProgramada' => $validated['horaProgramada'],
            'tipoAuditoria' => $validated['tipoAuditoria'],
            'estado' => $validated['estado'],
            'descripcion' => $validated['descripcion'],
            'nombreProceso' => $validated['nombreProceso'],
            'nombreEntidad' => $validated['nombreEntidad'],
            'auditorLider' => $validated['auditorLider'],
            'idProceso' => $proceso->idProceso
        ]);

        // Usuarios a notificar
        $notificados = collect();
        $emails = [];

        if ($validated['auditorLider']) {
            $auditorLider = Usuario::find($validated['auditorLider']);
            if ($auditorLider) {
                $notificados->push($auditorLider);
                $emails[] = $auditorLider->correo;
            }
        }

        $usuarioProceso = Usuario::find($proceso->idUsuario);
        if ($usuarioProceso) {
            $notificados->push($usuarioProceso);
            $emails[] = $usuarioProceso->correo;
        }

        $cronogramaData = [
            'tipoAuditoria' => $validated['tipoAuditoria'],
            'fechaProgramada' => $validated['fechaProgramada'],
            'horaProgramada' => $validated['horaProgramada'],
            'nombreProceso' => $validated['nombreProceso'],
            'nombreEntidad' => $validated['nombreEntidad']
        ];

        $userNames = $notificados->map(fn($u) => "{$u->nombre} {$u->apellidoPat} {$u->apellidoMat}")->toArray();

        foreach ($emails as $email) {
            try {
                Notification::route('mail', $email)->notify(
                    new AuditoriaNotificacion($cronogramaData, $userNames, $emails, 'actualizado')
                );
            } catch (\Throwable $e) {
                Log::error("Error al enviar correo a {$email}", ['error' => $e->getMessage()]);
            }
        }

        $notificados->each(function ($user) use ($cronogramaData, $userNames, $emails) {
            try {
                $user->notify(new AuditoriaNotificacion($cronogramaData, $userNames, $emails, 'actualizado'));
            } catch (\Throwable $e) {
                Log::error("Error al guardar notificación DB para usuario {$user->idUsuario}", ['error' => $e->getMessage()]);
            }
        });

        Log::info('Finalizando el método update');

        return response()->json(['message' => 'Auditoría actualizada y notificaciones enviadas']);
    }

    public function destroy($id)
    {
        Log::info('Iniciando el método destroy');

        $auditoria = Cronograma::findOrFail($id);

        // Obtener datos antes de eliminar
        $proceso = Proceso::where('nombreProceso', $auditoria->nombreProceso)->first();
        $usersList = [];
        $emails = [];

        if ($auditoria->auditorLider) {
            $auditorLider = Usuario::find($auditoria->auditorLider);
            if ($auditorLider) {
                $usersList[] = $auditorLider->nombre . ' ' . $auditorLider->apellidoPat . ' ' . $auditorLider->apellidoMat;
                $emails[] = $auditorLider->correo;
            }
        }

        if ($proceso) {
            $usuarioProceso = Usuario::find($proceso->idUsuario);
            if ($usuarioProceso) {
                $usersList[] = $usuarioProceso->nombre . ' ' . $usuarioProceso->apellidoPat . ' ' . $usuarioProceso->apellidoMat;
                $emails[] = $usuarioProceso->correo;
            }
        }

        $cronogramaData = [
            'tipoAuditoria' => $auditoria->tipoAuditoria,
            'fechaProgramada' => $auditoria->fechaProgramada,
            'horaProgramada' => $auditoria->horaProgramada,
            'nombreProceso' => $auditoria->nombreProceso,
            'nombreEntidad' => $auditoria->nombreEntidad
        ];

        foreach ($emails as $email) {
            Notification::route('mail', $email)->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'eliminado'));
        }

        if (isset($auditorLider))
            $auditorLider->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'eliminado'));
        if (isset($usuarioProceso))
            $usuarioProceso->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'eliminado'));

        $auditoria->delete();

        return response()->json(['message' => 'Auditoría eliminada y notificación enviada']);
    }

    public function todas(Request $request)
    {
        $rol = $request->query('rol');

        if (!in_array($rol, ['Administrador', 'Coordinador'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $auditorias = Cronograma::with(['proceso.entidad']) // asume relaciones correctas
            ->orderBy('fechaProgramada', 'desc')
            ->get();

        return response()->json($auditorias);
    }

    public function porSupervisor($idUsuario){
        //1. Obtener los procesos supervisados por el usuarios
        $procesosIds = SupervisorProceso::where('idUsuario', $idUsuario)->pluck('idProceso');

        //2. Obtener las auditorias de los procesos supervisados
        $auditorias = Cronograma::whereIn('idProceso', $procesosIds)->get();

        return response()->json($auditorias);
    }

}