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
    // JRH - 05/09/25 - Filtro para ver solo un tipo de proceso y geneal
    public function index(Request $request)
    {
        \Log::info('🔥 Request recibido:', [$request->all()]);
        \Log::info('📥 Petición al cronograma');
        $idUsuario = $request->input('idUsuario');
        $rolActivo = $request->input('rolActivo');
        \Log::info("🔐 Usuario: $idUsuario, Rol: $rolActivo");

        if ($rolActivo === 'Líder de Proceso') {
            $procesosUsuario = Proceso::where('idUsuario', $idUsuario)->pluck('idProceso');
            $auditorias = Cronograma::whereIn('idProceso', $procesosUsuario)->get();
            \Log::info('📊 Auditorías filtradas por líder de proceso', ['procesos' => $procesosUsuario]);
        } else {
            $auditorias = Cronograma::all();
            \Log::info('📊 Auditorías sin filtrar (rol con acceso total)');
        }

        return response()->json($auditorias);
    }


    /*public function store(Request $request)
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
            'fechaProgramada',
            'horaProgramada',
            'tipoAuditoria',
            'estado',
            'descripcion',
            'nombreProceso',
            'nombreEntidad',
            'auditorLider'
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
               // Enviar correo
               Notification::route('mail', $email)->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
                    Log::info('Notificación enviada por correo', ['email' => $email]);
                } catch (\Exception $e) {
                    Log::error('Error al enviar la notificación por correo', ['email' => $email, 'error' => $e->getMessage()]);
                }
        }

        // Enviar notificación por base de datos
        if ($request->auditorLider && isset($auditorLider)) {
            //$auditorLider->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails));
            $auditorLider->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
        
            Log::info('Notificación almacenada en database para auditor líder', ['idUsuario' => $auditorLider->idUsuario]);
        }

        if (isset($usuarioProceso)) {
            $usuarioProceso->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
            Log::info('Notificación almacenada en database para usuario del proceso', ['idUsuario' => $usuarioProceso->idUsuario]);
            }

        Log::info('Finalizando el método store');

        return response()->json([
            'message' => 'Auditoría guardada en el cronograma y notificación enviada',
            'auditoria' => $auditoria
        ], 201);
    }*/

    public function store(Request $request)
    {
        try {
            Log::info('Datos recibidos en store:', $request->all());

            $validatedData = $request->validate([
                'fechaProgramada' => 'required|date',
                'horaProgramada' => 'required|date_format:H:i',
                'tipoAuditoria' => 'required|in:interna,externa',
                'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
                'descripcion' => 'required|string',
                'idProceso' => 'required|integer|exists:proceso,idProceso', 
                'nombreProceso' => 'required|string|max:255',
                'nombreEntidad' => 'required|string|max:255',
                'auditorLider' => 'nullable|integer|exists:usuario,idUsuario' 
            ]);

            Log::info('Datos validados:', $validatedData);

            $proceso = Proceso::find($validatedData['idProceso']);
            
            if (!$proceso) {
                Log::error('Proceso no encontrado con ID:', ['idProceso' => $validatedData['idProceso']]);
                return response()->json([
                    'success' => false,
                    'message' => 'El proceso especificado no existe'
                ], 404);
            }

            $auditoria = Cronograma::create([
                'fechaProgramada' => $validatedData['fechaProgramada'],
                'horaProgramada' => $validatedData['horaProgramada'],
                'tipoAuditoria' => $validatedData['tipoAuditoria'],
                'estado' => $validatedData['estado'],
                'descripcion' => $validatedData['descripcion'],
                'idProceso' => $validatedData['idProceso'],
                'nombreProceso' => $validatedData['nombreProceso'],
                'nombreEntidad' => $validatedData['nombreEntidad'],
                'auditorLider' => $validatedData['auditorLider'] ?? null,
                'idUsuario' => $proceso->idUsuario
            ]);

            Log::info('Auditoría creada:', $auditoria->toArray());

            $usersList = [];
            $emails = [];

            if ($validatedData['auditorLider']) {
                $auditorLider = Usuario::find($validatedData['auditorLider']);
                if ($auditorLider) {
                    $usersList[] = $auditorLider->nombre . ' ' . $auditorLider->apellidoPat . ' ' . $auditorLider->apellidoMat;
                    $emails[] = $auditorLider->correo;
                    Log::info('Auditor líder encontrado:', ['auditor' => $auditorLider->toArray()]);
                }
            }

            $usuarioProceso = Usuario::find($proceso->idUsuario);
            if ($usuarioProceso) {
                $usersList[] = $usuarioProceso->nombre . ' ' . $usuarioProceso->apellidoPat . ' ' . $usuarioProceso->apellidoMat;
                $emails[] = $usuarioProceso->correo;
                Log::info('Usuario del proceso encontrado:', ['usuario' => $usuarioProceso->toArray()]);
            }

            $cronogramaData = [
                'tipoAuditoria' => $validatedData['tipoAuditoria'],
                'fechaProgramada' => $validatedData['fechaProgramada'],
                'horaProgramada' => $validatedData['horaProgramada'],
                'nombreProceso' => $validatedData['nombreProceso'],
                'nombreEntidad' => $validatedData['nombreEntidad'],
                'idProceso' => $validatedData['idProceso'],
                'idAuditoria' => $auditoria->id 
            ];

            foreach ($emails as $email) {
                try {
                    Notification::route('mail', $email)
                        ->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
                    Log::info('Notificación enviada a:', ['email' => $email]);
                } catch (\Exception $e) {
                    Log::error('Error enviando notificación:', [
                        'email' => $email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (isset($auditorLider)) {
                $auditorLider->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
            }

            if (isset($usuarioProceso)) {
                $usuarioProceso->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Auditoría creada exitosamente',
                'auditoria' => [
                    'idAuditoria' => $auditoria->id,
                    'nombreProceso' => $auditoria->nombreProceso,
                    'nombreEntidad' => $auditoria->nombreEntidad,
                    'tipoAuditoria' => $auditoria->tipoAuditoria,
                    'fechaProgramada' => $auditoria->fechaProgramada,
                    'horaProgramada' => $auditoria->horaProgramada,
                    'estado' => $auditoria->estado,
                    'descripcion' => $auditoria->descripcion,
                    'auditorLider' => $auditoria->auditorLider,
                    'idProceso' => $auditoria->idProceso
                ],
                'idProceso_enviado' => $validatedData['idProceso']
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error inesperado:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la auditoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Iniciando el método update');

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

        $auditoria = Cronograma::findOrFail($id);

        $proceso = Proceso::where('nombreProceso', $request->nombreProceso)->first();
        if (!$proceso) {
            return response()->json(['message' => 'El proceso no existe'], 404);
        }

        $auditoria->update([
            'fechaProgramada' => $request->fechaProgramada,
            'horaProgramada' => $request->horaProgramada,
            'tipoAuditoria' => $request->tipoAuditoria,
            'estado' => $request->estado,
            'descripcion' => $request->descripcion,
            'nombreProceso' => $request->nombreProceso,
            'nombreEntidad' => $request->nombreEntidad,
            'auditorLider' => $request->auditorLider,
            'idProceso' => $proceso->idProceso
        ]);

        // Obtener usuarios
        $usersList = [];
        $emails = [];

        if ($request->auditorLider) {
            $auditorLider = Usuario::find($request->auditorLider);
            if ($auditorLider) {
                $usersList[] = $auditorLider->nombre . ' ' . $auditorLider->apellidoPat . ' ' . $auditorLider->apellidoMat;
                $emails[] = $auditorLider->correo;
            }
        }

        $usuarioProceso = Usuario::find($proceso->idUsuario);
        if ($usuarioProceso) {
            $usersList[] = $usuarioProceso->nombre . ' ' . $usuarioProceso->apellidoPat . ' ' . $usuarioProceso->apellidoMat;
            $emails[] = $usuarioProceso->correo;
        }

        $cronogramaData = [
            'tipoAuditoria' => $request->tipoAuditoria,
            'fechaProgramada' => $request->fechaProgramada,
            'horaProgramada' => $request->horaProgramada,
            'nombreProceso' => $request->nombreProceso,
            'nombreEntidad' => $request->nombreEntidad
        ];

        foreach ($emails as $email) {
            Notification::route('mail', $email)->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'actualizado'));
        }

        if (isset($auditorLider))
            $auditorLider->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'actualizado'));
        if (isset($usuarioProceso))
            $usuarioProceso->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'actualizado'));

        return response()->json(['message' => 'Auditoría actualizada y notificación enviada']);
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

}

