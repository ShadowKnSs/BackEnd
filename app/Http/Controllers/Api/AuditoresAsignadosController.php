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
        $request->validate([
            'idAuditoria' => 'required|integer|exists:auditorias,idAuditoria',
            'auditores' => 'array',
            'auditores.*' => 'integer|exists:usuario,idUsuario',
            'auditorLider' => 'nullable|integer|exists:usuario,idUsuario', // opcional
        ]);

        $idAuditoria = (int) $request->idAuditoria;

        // Traer líder actual de la auditoría si no llega en el request
        $lider = $request->input('auditorLider');
        if (!$lider) {
            $lider = \DB::table('auditorias')->where('idAuditoria', $idAuditoria)->value('auditorLider');
        }

        $ids = collect($request->auditores ?? [])
            ->filter(fn($v) => is_numeric($v))
            ->map(fn($v) => (int) $v)
            ->when($lider, fn($c) => $c->push((int) $lider)) // incluir líder
            ->unique()
            ->values();

        \DB::transaction(function () use ($idAuditoria, $ids, $lider) {

            \DB::table('auditoresasignados')->where('idAuditoria', $idAuditoria)->delete();

            if ($ids->isNotEmpty()) {
                $usuarios = \DB::table('usuario')
                    ->whereIn('idUsuario', $ids)
                    ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat'])
                    ->keyBy('idUsuario');

                $rows = $ids->map(function ($idUsuario) use ($idAuditoria, $usuarios, $lider) {
                    $u = $usuarios->get($idUsuario);
                    $nombre = $u ? trim(preg_replace('/\s+/', ' ', ($u->nombre ?? '') . ' ' . ($u->apellidoPat ?? '') . ' ' . ($u->apellidoMat ?? ''))) : 'Nombre no disponible';
                    return [
                        'idAuditoria' => $idAuditoria,
                        'idUsuario' => $idUsuario,
                        'idAuditor' => $idUsuario,              // idAuditor≡idUsuario en tu esquema actual
                        'rol' => ($lider && (int) $lider === (int) $idUsuario) ? 'Lider' : 'Auditor'
                    ];
                })->all();

                \DB::table('auditoresasignados')->insert($rows);
            }
        });

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