<?php

namespace App\Http\Controllers\Api;

use App\Models\Cronograma;
use App\Models\SupervisorProceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Notifications\AuditoriaNotificacion;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\Usuario;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class CronogramaController extends Controller
{

    /** Borra notificaciones (canal database) de una auditoría para un set de usuarios */
    private function deleteDbNotificationsForAudit(array $userIds, int $idAuditoria): void
    {
        if (empty($userIds))
            return;

        $deleted = DatabaseNotification::query()
            ->whereIn('notifiable_id', $userIds)
            ->where('notifiable_type', Usuario::class)
            ->where('type', AuditoriaNotificacion::class)
            ->where(function ($q) use ($idAuditoria) {
                // Estructura actual (anidada): data->data->idAuditoria
                $q->where('data->data->idAuditoria', $idAuditoria)
                    // Fallback por si en algún momento aplanas la estructura
                    ->orWhere('data->idAuditoria', $idAuditoria);
            })
            ->delete();

        Log::info('deleteDbNotificationsForAudit', [
            'userIds' => $userIds,
            'idAuditoria' => $idAuditoria,
            'deleted' => $deleted
        ]);
    }

    /** Envía notificación (mail+database) a un set de users */
    private function notifyUsers(array $userIds, array $cronogramaData, array $usersList, array $emails, string $accion): void
    {
        if (empty($userIds))
            return;

        $usuarios = Usuario::whereIn('idUsuario', $userIds)->get();
        foreach ($usuarios as $u) {
            try {
                // via() = ['mail','database']; al notificar al modelo, dispara ambos canales
                $u->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, $accion));
            } catch (\Throwable $e) {
                \Log::error("Error notificando a usuario {$u->idUsuario}", ['err' => $e->getMessage()]);
            }
        }
    }

    public function index(Request $request)
    {
        // Validar idProceso + rango opcional
        $validated = $request->validate([
            'idProceso' => 'required|integer|exists:proceso,idProceso',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
        ]);

        $idProceso = (int) $validated['idProceso'];
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        // Si mandan el rango invertido, lo normalizamos
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        \Log::info("Consultando auditorías para idProceso={$idProceso}, rango", compact('from', 'to'));

        $q = \DB::table('auditorias as a')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->leftJoin('usuario as ul', 'ul.idUsuario', '=', 'a.auditorLider')
            ->where('a.idProceso', $idProceso)
            ->select([
                'a.idAuditoria',
                'a.fechaProgramada',
                'a.horaProgramada',
                'a.tipoAuditoria',
                'a.estado',
                'a.descripcion',
                'a.idProceso',
                'p.nombreProceso',
                'e.nombreEntidad',
                'a.auditorLider',
                \DB::raw("TRIM(CONCAT(COALESCE(ul.nombre,''),' ',COALESCE(ul.apellidoPat,''),' ',COALESCE(ul.apellidoMat,''))) as nombreAuditorLider")
            ]);

        // Filtro por rango visible si viene from/to
        if ($from && $to) {
            $q->whereBetween('a.fechaProgramada', [$from, $to]);
        } elseif ($from) {
            $q->whereDate('a.fechaProgramada', '>=', $from);
        } elseif ($to) {
            $q->whereDate('a.fechaProgramada', '<=', $to);
        }

        $rows = $q
            ->orderBy('a.fechaProgramada', 'asc')
            ->orderBy('a.horaProgramada', 'asc')
            ->get();

        return response()->json($rows, 200);
    }


    public function store(Request $request)
    {
        try {
            Log::info('Datos recibidos en store:', $request->all());

            // Normaliza casing
            $request->merge([
                'tipoAuditoria' => strtolower($request->input('tipoAuditoria', '')),
                'estado' => ucfirst(strtolower($request->input('estado', ''))),
            ]);

            $validated = $request->validate([
                'fechaProgramada' => 'required|date',
                'horaProgramada' => 'required|date_format:H:i',
                'tipoAuditoria' => 'required|in:interna,externa',
                'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
                'descripcion' => 'required|string|max:512',

                // preferente: por id
                'idProceso' => 'nullable|integer|exists:proceso,idProceso',

                // compat: solo para RESOLVER idProceso (NO guardar)
                'nombreProceso' => 'nullable|string|max:255',
                'nombreEntidad' => 'nullable|string|max:255',

                // ids de usuario (en tu esquema idAuditor ≡ idUsuario)
                'auditorLider' => 'nullable|integer|exists:usuario,idUsuario',
                'auditoresAdicionales' => 'array',
                'auditoresAdicionales.*' => 'integer|exists:usuario,idUsuario',
            ]);

            // Validación adicional: hora dentro del horario SGC (08:00 - 17:00)
            $hora = $validated['horaProgramada'] ?? $request->input('horaProgramada');
            if ($hora) {
                if ($hora < '08:00' || $hora > '17:00') {
                    return response()->json(['success' => false, 'message' => 'La hora debe estar entre 08:00 y 17:00.'], 422);
                }
            }

            // Resolver $proceso / $idProceso
            $proceso = null;
            if (!empty($validated['idProceso'])) {
                $proceso = Proceso::with('entidad')->find($validated['idProceso']);
                if (!$proceso) {
                    return response()->json(['success' => false, 'message' => 'Proceso inválido'], 404);
                }
            } else {
                if (empty($validated['nombreProceso']) || empty($validated['nombreEntidad'])) {
                    return response()->json(['success' => false, 'message' => 'Falta idProceso o (nombreProceso + nombreEntidad)'], 422);
                }
                $entidad = EntidadDependencia::where('nombreEntidad', $validated['nombreEntidad'])
                    ->where('activo', 1)->first();
                if (!$entidad) {
                    return response()->json(['success' => false, 'message' => 'Entidad inválida'], 404);
                }
                $proceso = Proceso::with('entidad')
                    ->where('nombreProceso', $validated['nombreProceso'])
                    ->where('idEntidad', $entidad->idEntidadDependencia)
                    ->first();
                if (!$proceso) {
                    return response()->json(['success' => false, 'message' => 'Proceso inválido para la entidad'], 404);
                }
            }
            $idProceso = $proceso->idProceso;

            // Invariante para EXTERNA
            $isExterna = $validated['tipoAuditoria'] === 'externa';
            if ($isExterna) {
                // Fuerza líder nulo y NO considerar adicionales
                $validated['auditorLider'] = null;
            }

            // Lista de auditores para validar solape (solo internas)
            $idsAuditores = [];
            if (!$isExterna) {
                $idsAuditores = collect($request->input('auditoresAdicionales', []))
                    ->when($validated['auditorLider'] ?? null, fn($c) => $c->push((int) $validated['auditorLider']))
                    ->map(fn($v) => (int) $v)->unique()->values()->all();
            }

            if (!empty($idsAuditores)) {
                $conflicto = $this->existeChoque(
                    $idsAuditores,
                    $validated['fechaProgramada'],
                    $validated['horaProgramada'],
                    null
                );
                if ($conflicto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Conflicto de horario: uno o más auditores ya tienen una auditoría en ese momento.'
                    ], 422);
                }
            }

            // Crear + asignar en transacción
            $auditoria = DB::transaction(function () use ($validated, $idProceso, $idsAuditores, $isExterna) {
                $auditoria = Cronograma::create([
                    'fechaProgramada' => $validated['fechaProgramada'],
                    'horaProgramada' => $validated['horaProgramada'],
                    'tipoAuditoria' => $validated['tipoAuditoria'],
                    'estado' => $validated['estado'],
                    'descripcion' => $validated['descripcion'],
                    'idProceso' => $idProceso,
                    'auditorLider' => $validated['auditorLider'] ?? null, // será null si es externa
                ]);

                // Asignaciones: SOLO si NO es externa
                DB::table('auditoresasignados')->where('idAuditoria', $auditoria->idAuditoria)->delete();
                if (!$isExterna && !empty($idsAuditores)) {
                    $rows = collect($idsAuditores)->map(function ($idUsuario) use ($auditoria, $validated) {
                        return [
                            'idAuditoria' => $auditoria->idAuditoria,
                            'idUsuario' => $idUsuario,
                            'idAuditor' => $idUsuario, // en tu esquema actual
                            'rol' => ($validated['auditorLider'] ?? null) == $idUsuario ? 'Lider' : 'Auditor',
                        ];
                    })->all();
                    DB::table('auditoresasignados')->insert($rows);
                }

                return $auditoria;
            });

            // Notificaciones
            $usersList = [];
            $emails = [];

            if (!empty($validated['auditorLider'])) {
                $auditorLider = Usuario::find($validated['auditorLider']);
                if ($auditorLider) {
                    $usersList[] = "{$auditorLider->nombre} {$auditorLider->apellidoPat} {$auditorLider->apellidoMat}";
                    $emails[] = $auditorLider->correo;
                }
            }

            if (!empty($proceso->idUsuario)) {
                $usuarioProceso = Usuario::find($proceso->idUsuario);
                if ($usuarioProceso) {
                    $usersList[] = "{$usuarioProceso->nombre} {$usuarioProceso->apellidoPat} {$usuarioProceso->apellidoMat}";
                    $emails[] = $usuarioProceso->correo;
                }
            }

            $cronogramaData = [
                'tipoAuditoria' => $validated['tipoAuditoria'],
                'fechaProgramada' => $validated['fechaProgramada'],
                'horaProgramada' => $validated['horaProgramada'],
                'idProceso' => $idProceso,
                'idAuditoria' => $auditoria->idAuditoria,
                'nombreProceso' => $proceso->nombreProceso ?? null,
                'nombreEntidad' => optional($proceso->entidad)->nombreEntidad ?? null,
            ];

            foreach ($emails as $email) {
                try {
                    Notification::route('mail', $email)
                        ->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
                } catch (\Throwable $e) {
                    Log::error('Error enviando notificación', ['email' => $email, 'error' => $e->getMessage()]);
                }
            }
            if (isset($auditorLider))
                $auditorLider->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));
            if (isset($usuarioProceso))
                $usuarioProceso->notify(new AuditoriaNotificacion($cronogramaData, $usersList, $emails, 'creado'));

            return response()->json([
                'success' => true,
                'message' => 'Auditoría creada exitosamente',
                'auditoria' => [
                    'idAuditoria' => $auditoria->idAuditoria,
                    'tipoAuditoria' => $auditoria->tipoAuditoria,
                    'fechaProgramada' => $auditoria->fechaProgramada,
                    'horaProgramada' => $auditoria->horaProgramada,
                    'estado' => $auditoria->estado,
                    'descripcion' => $auditoria->descripcion,
                    'auditorLider' => $auditoria->auditorLider, // null si externa
                    'idProceso' => $auditoria->idProceso,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error inesperado:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la auditoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        Log::info('Iniciando update', ['id' => $id]);

        // Normaliza (opcional si el front ya envía bien)
        $request->merge([
            'tipoAuditoria' => strtolower($request->input('tipoAuditoria', '')),
            'estado' => ucfirst(strtolower($request->input('estado', ''))),
        ]);

        $validated = $request->validate([
            'fechaProgramada' => 'required|date',
            'horaProgramada' => 'required|date_format:H:i',
            'tipoAuditoria' => 'required|in:interna,externa',
            'estado' => 'required|in:Pendiente,Finalizada,Cancelada',
            'descripcion' => 'required|string|max:512',

            'idProceso' => 'nullable|integer|exists:proceso,idProceso',
            'nombreProceso' => 'nullable|string|max:255',
            'nombreEntidad' => 'nullable|string|max:255',

            'auditorLider' => 'nullable|integer|exists:usuario,idUsuario',
        ]);

        $auditoria = Cronograma::with('asignados')->findOrFail($id);

        $oldProceso = Proceso::with('entidad')->find($auditoria->idProceso);
        $oldOwnerId = $oldProceso?->idUsuario;           // líder del proceso X (antes)
        $oldLeaderId = $auditoria->auditorLider;          // auditor líder anterior

        // Resolver $proceso / $idProceso
        if (!empty($validated['idProceso'])) {
            $proceso = Proceso::with('entidad')->find($validated['idProceso']);
            if (!$proceso)
                return response()->json(['message' => 'Proceso inválido'], 404);
        } elseif (!empty($validated['nombreProceso']) && !empty($validated['nombreEntidad'])) {
            $entidad = EntidadDependencia::where('nombreEntidad', $validated['nombreEntidad'])
                ->where('activo', 1)->first();
            if (!$entidad)
                return response()->json(['message' => 'Entidad inválida'], 404);

            $proceso = Proceso::with('entidad')
                ->where('nombreProceso', $validated['nombreProceso'])
                ->where('idEntidad', $entidad->idEntidadDependencia)
                ->first();
            if (!$proceso)
                return response()->json(['message' => 'Proceso inválido para la entidad'], 404);
        } else {
            $proceso = Proceso::with('entidad')->find($auditoria->idProceso);
            if (!$proceso)
                return response()->json(['message' => 'Proceso actual no encontrado'], 404);
        }
        $idProceso = $proceso->idProceso;

        // Invariante para EXTERNA
        $isExterna = $validated['tipoAuditoria'] === 'externa';
        if ($isExterna) {
            // Fuerza líder nulo si la auditoría es externa
            $validated['auditorLider'] = null;
        }

        // Solapamiento: solo tiene sentido si NO es externa (porque externas no tendrán asignados)
        $idsAuditores = collect();
        if (!$isExterna) {
            $idsAuditores = $auditoria->asignados()
                ->pluck('idAuditor')->map(fn($v) => (int) $v)->filter()->unique()->values();

            if (!empty($validated['auditorLider'])) {
                $idsAuditores = $idsAuditores->push((int) $validated['auditorLider'])->unique()->values();
            }

            if ($idsAuditores->isNotEmpty()) {
                $conflicto = $this->existeChoque(
                    $idsAuditores->all(),
                    $validated['fechaProgramada'],
                    $validated['horaProgramada'],
                    (int) $auditoria->idAuditoria
                );
                if ($conflicto) {
                    return response()->json(['message' => 'Conflicto de horario al reprogramar.'], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            // Persistencia principal
            $auditoria->update([
                'fechaProgramada' => $validated['fechaProgramada'],
                'horaProgramada' => $validated['horaProgramada'],
                'tipoAuditoria' => $validated['tipoAuditoria'],
                'estado' => $validated['estado'],
                'descripcion' => $validated['descripcion'],
                'idProceso' => $idProceso,
                'auditorLider' => $validated['auditorLider'] ?? $auditoria->auditorLider, // será null si externa
            ]);

            // Sincronización de asignaciones
            if ($isExterna) {
                // Borrar TODAS las asignaciones si es externa
                DB::table('auditoresasignados')
                    ->where('idAuditoria', $auditoria->idAuditoria)
                    ->delete();
            } else {

            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en update()', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al actualizar la auditoría'], 500);
        }

        // Notificaciones
        // ===== Sincronización de notificaciones (post-commit) =====
        $newOwnerId = $proceso?->idUsuario;               // líder del proceso Y (nuevo)
        $newLeaderId = $auditoria->auditorLider;           // líder auditor NUEVO

        // Conjuntos a comparar (ignorando nulls)
        $oldCore = collect([$oldOwnerId, $oldLeaderId])->filter()->unique()->values();
        $newCore = collect([$newOwnerId, $newLeaderId])->filter()->unique()->values();

        $toRemove = $oldCore->diff($newCore)->values()->all(); // quitar notificación
        $toAdd = $newCore->diff($oldCore)->values()->all(); // crear notificación

        // Prepara datos de la notificación
        $cronogramaData = [
            'idAuditoria' => $auditoria->idAuditoria,
            'idProceso' => $idProceso,
            'tipoAuditoria' => $validated['tipoAuditoria'],
            'fechaProgramada' => $validated['fechaProgramada'],
            'horaProgramada' => $validated['horaProgramada'],
            'nombreProceso' => $proceso->nombreProceso ?? null,
            'nombreEntidad' => optional($proceso->entidad)->nombreEntidad ?? null,
        ];

        // (Opcional) nombres para el cuerpo del correo
        $usersList = [];
        $emails = [];
        foreach ([$newLeaderId, $newOwnerId] as $uid) {
            if (!$uid)
                continue;
            $u = Usuario::find($uid);
            if ($u) {
                $usersList[] = trim("{$u->nombre} {$u->apellidoPat} {$u->apellidoMat}");
                $emails[] = $u->correo;
            }
        }

        // 1) Elimina notificación en DB a quienes ya no corresponde
        $this->deleteDbNotificationsForAudit($toRemove, (int) $auditoria->idAuditoria);

        // 2) Notifica (DB + correo) a los nuevos receptores
        $this->notifyUsers($toAdd, $cronogramaData, $usersList, $emails, 'actualizado');

        return response()->json(['message' => 'Auditoría actualizada y notificaciones sincronizadas']);
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
        $rol = $request->query('rol'); // Administrador | Coordinador
        $from = $request->query('from'); // YYYY-MM-DD
        $to = $request->query('to');   // YYYY-MM-DD

        $request->validate([
            'rol' => 'required|string|in:Administrador,Coordinador de Calidad',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
        ]);

        $q = DB::table('auditorias as a')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->leftJoin('usuario as u', 'u.idUsuario', '=', 'a.auditorLider')
            ->selectRaw("
            a.idAuditoria,
            a.fechaProgramada,
            a.horaProgramada,
            a.tipoAuditoria,
            a.estado,
            a.descripcion,
            a.idProceso,
            p.nombreProceso as nombreProceso,
            e.nombreEntidad as nombreEntidad,
            a.auditorLider,
            CONCAT(COALESCE(u.nombre,''),' ',COALESCE(u.apellidoPat,''),' ',COALESCE(u.apellidoMat,'')) as nombreAuditorLider
        ")
            ->orderBy('a.fechaProgramada', 'asc')
            ->orderBy('a.horaProgramada', 'asc');

        // Filtro por rango visible si viene from/to
        if ($from && $to) {
            $q->whereBetween('a.fechaProgramada', [$from, $to]);
        } elseif ($from) {
            $q->whereDate('a.fechaProgramada', '>=', $from);
        } elseif ($to) {
            $q->whereDate('a.fechaProgramada', '<=', $to);
        }

        $rows = $q->get();

        return response()->json($rows);
    }


    public function porLider($idUsuario)
    {
        // Todas las auditorías de procesos cuyo dueño (idUsuario) es el líder dado
        $rows = \DB::table('auditorias as a')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->leftJoin('usuario as ul', 'ul.idUsuario', '=', 'a.auditorLider')
            ->where('p.idUsuario', $idUsuario)
            ->select([
                'a.idAuditoria',
                'a.fechaProgramada',
                'a.horaProgramada',
                'a.tipoAuditoria',
                'a.estado',
                'a.descripcion',
                'a.idProceso',
                'p.nombreProceso',
                'e.nombreEntidad',
                'a.auditorLider',
                \DB::raw("TRIM(CONCAT(COALESCE(ul.nombre,''),' ',COALESCE(ul.apellidoPat,''),' ',COALESCE(ul.apellidoMat,''))) as nombreAuditorLider")
            ])
            ->orderBy('a.fechaProgramada', 'desc')
            ->get();

        return response()->json($rows);
    }
    public function porSupervisor($idUsuario)
    {
        //  Mantén la lógica, pero devuelve los mismos campos que /todas
        $rows = \DB::table('auditorias as a')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->leftJoin('usuario as ul', 'ul.idUsuario', '=', 'a.auditorLider')
            ->join('supervisor_proceso as sp', 'sp.idProceso', '=', 'p.idProceso')
            ->where('sp.idUsuario', $idUsuario)
            ->select([
                'a.idAuditoria',
                'a.fechaProgramada',
                'a.horaProgramada',
                'a.tipoAuditoria',
                'a.estado',
                'a.descripcion',
                'a.idProceso',
                'p.nombreProceso',
                'e.nombreEntidad',
                'a.auditorLider',
                \DB::raw("TRIM(CONCAT(COALESCE(ul.nombre,''),' ',COALESCE(ul.apellidoPat,''),' ',COALESCE(ul.apellidoMat,''))) as nombreAuditorLider")
            ])
            ->orderBy('a.fechaProgramada', 'desc')
            ->get();

        return response()->json($rows);
    }

    // App\Http\Controllers\Api\CronogramaController.php

    public function porAuditor($idUsuario)
    {
        // Auditorías donde el usuario aparece asignado (incluye líder y adicionales)
        $rows = \DB::table('auditorias as a')
            ->join('auditoresasignados as aa', 'aa.idAuditoria', '=', 'a.idAuditoria')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->leftJoin('usuario as ul', 'ul.idUsuario', '=', 'a.auditorLider')
            ->where('aa.idUsuario', $idUsuario)
            ->select([
                'a.idAuditoria',
                'a.fechaProgramada',
                'a.horaProgramada',
                'a.tipoAuditoria',
                'a.estado',
                'a.descripcion',
                'a.idProceso',
                'p.nombreProceso',
                'e.nombreEntidad',
                'a.auditorLider',
                \DB::raw("TRIM(CONCAT(COALESCE(ul.nombre,''),' ',COALESCE(ul.apellidoPat,''),' ',COALESCE(ul.apellidoMat,''))) as nombreAuditorLider")
            ])
            ->orderBy('a.fechaProgramada', 'desc')
            ->get();

        return response()->json($rows);
    }

    private function existeChoque(array $idAuditores, string $fecha, string $hora, ?int $excluirId = null, int $durMins = 60): bool
    {
        // Intervalo de la NUEVA auditoría
        $newStart = Carbon::parse("{$fecha} {$hora}:00");
        $newEnd = $newStart->copy()->addMinutes($durMins);

        // Regla de solape (half-open): A < newEnd  &&  A+dur > newStart
        // Y limitamos a MISMO DÍA por performance y por tu requerimiento
        return \DB::table('auditorias as a')
            ->join('auditoresasignados as aa', 'aa.idAuditoria', '=', 'a.idAuditoria')
            ->when($excluirId, fn($q) => $q->where('a.idAuditoria', '!=', $excluirId))
            ->whereIn('aa.idAuditor', $idAuditores)           // en tu esquema ≡ idUsuario
            ->where('a.estado', '!=', 'Cancelada')
            ->whereDate('a.fechaProgramada', $fecha)          // mismo día
            ->whereRaw("
            TIMESTAMP(a.fechaProgramada, a.horaProgramada) < ?
            AND TIMESTAMPADD(MINUTE, ?, TIMESTAMP(a.fechaProgramada, a.horaProgramada)) > ?
        ", [$newEnd->toDateTimeString(), $durMins, $newStart->toDateTimeString()])
            ->exists();
    }

}