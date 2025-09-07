<?php

namespace App\Http\Controllers\Api;

use App\Models\Cronograma;
use App\Models\SupervisorProceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Notifications\AuditoriaNotificacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;



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

            // Resolver $proceso y $idProceso (una sola variable unificada)
            $proceso = null;
            if (!empty($validated['idProceso'])) {
                $proceso = Proceso::with('entidad')->find($validated['idProceso']);
                if (!$proceso) {
                    return response()->json(['success' => false, 'message' => 'Proceso inválido'], 404);
                }
            } else {
                // Fallback por nombres
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

            // Lista de auditores para validar solape (idAuditor ≡ idUsuario en tu tabla actual)
            $idsAuditores = collect($request->input('auditoresAdicionales', []))
                ->when($validated['auditorLider'] ?? null, fn($c) => $c->push((int) $validated['auditorLider']))
                ->map(fn($v) => (int) $v)->unique()->values()->all();

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
            $auditoria = DB::transaction(function () use ($validated, $idProceso, $idsAuditores) {
                $auditoria = Cronograma::create([
                    'fechaProgramada' => $validated['fechaProgramada'],
                    'horaProgramada' => $validated['horaProgramada'],
                    'tipoAuditoria' => $validated['tipoAuditoria'],
                    'estado' => $validated['estado'],
                    'descripcion' => $validated['descripcion'],
                    'idProceso' => $idProceso,
                    'auditorLider' => $validated['auditorLider'] ?? null,
                ]);

                // Asignaciones (incluye líder con rol)
                DB::table('auditoresasignados')->where('idAuditoria', $auditoria->idAuditoria)->delete();

                $ids = collect($idsAuditores);
                if ($ids->isNotEmpty()) {
                    $rows = $ids->map(function ($idUsuario) use ($auditoria, $validated) {
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

            // Notificaciones (si las quieres mantener, sin usar $proc ni nombres guardados)
            $usersList = [];
            $emails = [];

            if (!empty($validated['auditorLider'])) {
                $auditorLider = Usuario::find($validated['auditorLider']);
                if ($auditorLider) {
                    $usersList[] = "{$auditorLider->nombre} {$auditorLider->apellidoPat} {$auditorLider->apellidoMat}";
                    $emails[] = $auditorLider->correo;
                }
            }

            // Usuario dueño del proceso (si aplica)
            if (!empty($proceso->idUsuario)) {
                $usuarioProceso = Usuario::find($proceso->idUsuario);
                if ($usuarioProceso) {
                    $usersList[] = "{$usuarioProceso->nombre} {$usuarioProceso->apellidoPat} {$usuarioProceso->apellidoMat}";
                    $emails[] = $usuarioProceso->correo;
                }
            }

            // Si quieres incluir nombres en el correo, tómalo de $proceso y su entidad
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

            // Respuesta
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
                    'auditorLider' => $auditoria->auditorLider,
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
        'estado'        => ucfirst(strtolower($request->input('estado', ''))),
    ]);

    $validated = $request->validate([
        'fechaProgramada' => 'required|date',
        'horaProgramada'  => 'required|date_format:H:i',
        'tipoAuditoria'   => 'required|in:interna,externa',
        'estado'          => 'required|in:Pendiente,Finalizada,Cancelada',
        'descripcion'     => 'required|string|max:512',

        // ✅ tabla correcta
        'idProceso'       => 'nullable|integer|exists:proceso,idProceso',

        // compat: solo para resolver idProceso si aún llega
        'nombreProceso'   => 'nullable|string|max:255',
        'nombreEntidad'   => 'nullable|string|max:255',

        'auditorLider'    => 'nullable|integer|exists:usuario,idUsuario',
    ]);

    $auditoria = Cronograma::with('asignados')->findOrFail($id);

    // -------- Resolver $proceso y $idProceso (siempre definidos) --------
    if (!empty($validated['idProceso'])) {
        $proceso = Proceso::with('entidad')->find($validated['idProceso']);
        if (!$proceso) return response()->json(['message' => 'Proceso inválido'], 404);
    } elseif (!empty($validated['nombreProceso']) && !empty($validated['nombreEntidad'])) {
        $entidad = EntidadDependencia::where('nombreEntidad', $validated['nombreEntidad'])
                    ->where('activo', 1)->first();
        if (!$entidad) return response()->json(['message' => 'Entidad inválida'], 404);

        $proceso = Proceso::with('entidad')
                    ->where('nombreProceso', $validated['nombreProceso'])
                    ->where('idEntidad', $entidad->idEntidadDependencia)
                    ->first();
        if (!$proceso) return response()->json(['message' => 'Proceso inválido para la entidad'], 404);
    } else {
        // Ni id ni nombres: usa el proceso actual de la auditoría
        $proceso = Proceso::with('entidad')->find($auditoria->idProceso);
        if (!$proceso) return response()->json(['message' => 'Proceso actual no encontrado'], 404);
    }
    $idProceso = $proceso->idProceso;

    // -------- Solapamiento por idAuditor (≡ idUsuario en tu esquema) --------
    $idsAuditores = $auditoria->asignados()
        ->pluck('idAuditor')->map(fn($v) => (int)$v)->filter()->unique()->values();

    if (!empty($validated['auditorLider'])) {
        $idsAuditores = $idsAuditores->push((int)$validated['auditorLider'])->unique()->values();
    }

    if ($idsAuditores->isNotEmpty()) {
        $conflicto = $this->existeChoque(
            $idsAuditores->all(),
            $validated['fechaProgramada'],
            $validated['horaProgramada'],
            (int)$auditoria->idAuditoria
        );
        if ($conflicto) {
            return response()->json(['message' => 'Conflicto de horario al reprogramar.'], 422);
        }
    }

    // -------- Persistencia --------
    $auditoria->update([
        'fechaProgramada' => $validated['fechaProgramada'],
        'horaProgramada'  => $validated['horaProgramada'],
        'tipoAuditoria'   => $validated['tipoAuditoria'],
        'estado'          => $validated['estado'],
        'descripcion'     => $validated['descripcion'],
        'idProceso'       => $idProceso,
        'auditorLider'    => $validated['auditorLider'] ?? $auditoria->auditorLider,
    ]);

    // -------- Notificaciones (sin $proc) --------
    $notificados = collect();
    $emails = [];

    if (!empty($validated['auditorLider'])) {
        $auditorLider = Usuario::find($validated['auditorLider']);
        if ($auditorLider) {
            $notificados->push($auditorLider);
            $emails[] = $auditorLider->correo;
        }
    }

    if (!empty($proceso->idUsuario)) {
        $usuarioProceso = Usuario::find($proceso->idUsuario);
        if ($usuarioProceso) {
            $notificados->push($usuarioProceso);
            $emails[] = $usuarioProceso->correo;
        }
    }

    $cronogramaData = [
        'tipoAuditoria'   => $validated['tipoAuditoria'],
        'fechaProgramada' => $validated['fechaProgramada'],
        'horaProgramada'  => $validated['horaProgramada'],
        // Nombres tomados del modelo, no del request
        'nombreProceso'   => $proceso->nombreProceso ?? null,
        'nombreEntidad'   => optional($proceso->entidad)->nombreEntidad ?? null,
        'idProceso'       => $idProceso,
        'idAuditoria'     => $auditoria->idAuditoria,
    ];

    $userNames = $notificados->map(fn($u) => "{$u->nombre} {$u->apellidoPat} {$u->apellidoMat}")->toArray();

    foreach ($emails as $email) {
        try {
            Notification::route('mail', $email)
                ->notify(new AuditoriaNotificacion($cronogramaData, $userNames, $emails, 'actualizado'));
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

        $rows = \DB::table('auditorias as a')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->leftJoin('usuario as ul', 'ul.idUsuario', '=', 'a.auditorLider')
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
        //1. Obtener los procesos supervisados por el usuarios
        $procesosIds = SupervisorProceso::where('idUsuario', $idUsuario)->pluck('idProceso');

        //2. Obtener las auditorias de los procesos supervisados
        $auditorias = Cronograma::whereIn('idProceso', $procesosIds)->get();

        return response()->json($auditorias);
    }


    private function existeChoque(array $idAuditores, string $fecha, string $hora, ?int $excluirId = null): bool
    {
        // Construir un DATETIME de referencia (inicio)
        $inicio = Carbon::parse("{$fecha} {$hora}:00");

        // Como no manejas 'fin', tratamos cada auditoría como un punto en el tiempo.
        // Definimos una ventana de colisión de 1 minuto (mismo minuto = conflicto).
        $min = $inicio->copy()->startOfMinute();
        $max = $inicio->copy()->endOfMinute();

        return \DB::table('auditorias as a')
            ->join('auditoresasignados as aa', 'aa.idAuditoria', '=', 'a.idAuditoria')
            ->when($excluirId, fn($q) => $q->where('a.idAuditoria', '!=', $excluirId))
            ->whereIn('aa.idAuditor', $idAuditores)        // <<< clave: por idAuditor
            ->where('a.estado', '!=', 'Cancelada')
            ->whereBetween(
                \DB::raw("STR_TO_DATE(CONCAT(a.fechaProgramada,' ',a.horaProgramada), '%Y-%m-%d %H:%i:%s')"),
                [$min->toDateTimeString(), $max->toDateTimeString()]
            )
            ->exists();
    }
}