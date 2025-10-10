<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditoresAsignados;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;
use App\Notifications\AuditoriaNotificacion;
use Illuminate\Support\Facades\Log;




class AuditoresAsignadosController extends Controller
{
    /** Borra notificaciones (canal database) de una auditoría para un set de usuarios */
   private function deleteDbNotificationsForAudit(array $userIds, int $idAuditoria): void
{
    if (empty($userIds)) return;

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
                $u->notify(new \App\Notifications\AuditoriaNotificacion($cronogramaData, $usersList, $emails, $accion));
            } catch (\Throwable $e) {
                \Log::error("Error notificando a usuario {$u->idUsuario}", ['err' => $e->getMessage()]);
            }
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'idAuditoria' => 'required|integer|exists:auditorias,idAuditoria',
            'auditores' => 'array',
            'auditores.*' => 'integer|exists:usuario,idUsuario',
            'auditorLider' => 'nullable|integer|exists:usuario,idUsuario',
        ]);

        $idAuditoria = (int) $request->idAuditoria;

        // LEE líder de request o de la auditoría
        $lider = $request->input('auditorLider');
        if (!$lider) {
            $lider = \DB::table('auditorias')->where('idAuditoria', $idAuditoria)->value('auditorLider');
        }

        // NUEVO conjunto de asignados (incluye líder si existe)
        $newIds = collect($request->auditores ?? [])
            ->filter(fn($v) => is_numeric($v))
            ->map(fn($v) => (int) $v)
            ->when($lider, fn($c) => $c->push((int) $lider))
            ->unique()->values();

        // CONJUNTO anterior (antes de borrar/insertar)
        $oldIds = \DB::table('auditoresasignados')
            ->where('idAuditoria', $idAuditoria)
            ->pluck('idUsuario')->map(fn($v) => (int) $v)->unique()->values();

        $toRemove = $oldIds->diff($newIds)->values()->all();
        $toAdd = $newIds->diff($oldIds)->values()->all();

        // Reemplaza asignaciones
        \DB::transaction(function () use ($idAuditoria, $newIds, $lider) {
            \DB::table('auditoresasignados')->where('idAuditoria', $idAuditoria)->delete();

            if ($newIds->isNotEmpty()) {
                $usuarios = \DB::table('usuario')
                    ->whereIn('idUsuario', $newIds)
                    ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat'])
                    ->keyBy('idUsuario');

                $rows = $newIds->map(function ($idUsuario) use ($idAuditoria, $usuarios, $lider) {
                    return [
                        'idAuditoria' => $idAuditoria,
                        'idUsuario' => $idUsuario,
                        'idAuditor' => $idUsuario,
                        'rol' => ($lider && (int) $lider === (int) $idUsuario) ? 'Lider' : 'Auditor',
                    ];
                })->all();

                \DB::table('auditoresasignados')->insert($rows);
            }
        });

        // ===== Sincroniza notificaciones para asignados =====
        // Carga información de auditoría/proceso para armar cronogramaData
        $a = \DB::table('auditorias as a')
            ->join('proceso as p', 'p.idProceso', '=', 'a.idProceso')
            ->join('entidaddependencia as e', 'e.idEntidadDependencia', '=', 'p.idEntidad')
            ->select('a.*', 'p.nombreProceso', 'e.nombreEntidad')
            ->where('a.idAuditoria', $idAuditoria)->first();

        if ($a) {
            $cronogramaData = [
                'idAuditoria' => (int) $a->idAuditoria,
                'idProceso' => (int) $a->idProceso,
                'tipoAuditoria' => $a->tipoAuditoria,
                'fechaProgramada' => $a->fechaProgramada,
                'horaProgramada' => $a->horaProgramada,
                'nombreProceso' => $a->nombreProceso ?? null,
                'nombreEntidad' => $a->nombreEntidad ?? null,
            ];

            // nombres/emails (opcional para el correo)
            $usersList = [];
            $emails = [];
            $uInfos = \DB::table('usuario')->whereIn('idUsuario', $newIds)->get();
            foreach ($uInfos as $u) {
                $usersList[] = trim("{$u->nombre} {$u->apellidoPat} {$u->apellidoMat}");
                $emails[] = $u->correo;
            }

            // Elimina notificaciones de los que YA NO están asignados
            $this->deleteDbNotificationsForAudit($toRemove, (int) $a->idAuditoria);

            // Crea notificación (DB + correo) para los NUEVOS asignados
            $this->notifyUsers($toAdd, $cronogramaData, $usersList, $emails, 'actualizado');
        }

        return response()->json(['ok' => true]);
    }


    public function show($idAuditoria)
    {
        $id = (int) $idAuditoria;

        $rows = \DB::table('auditoresasignados as aa')
            ->leftJoin('usuario as u', 'u.idUsuario', '=', 'aa.idUsuario')
            ->where('aa.idAuditoria', $id)
            ->get([
                'aa.idUsuario',
                'aa.idAuditor',
                'aa.rol',
                \DB::raw("TRIM(CONCAT(
                COALESCE(u.nombre,''),' ',
                COALESCE(u.apellidoPat,''),' ',
                COALESCE(u.apellidoMat,'')
            )) as nombreCompleto")
            ]);

        $data = $rows->map(fn($r) => [
            'idUsuario' => (int) $r->idUsuario,
            'idAuditor' => (int) $r->idAuditor,
            'rol' => $r->rol ?? 'Auditor',
            'nombreCompleto' => $r->nombreCompleto !== '' ? $r->nombreCompleto : 'Nombre no disponible',
        ]);

        return response()->json($data);
    }



}