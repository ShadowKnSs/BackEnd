<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\TipoUsuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'apellidoPat' => 'required|string',
            'apellidoMat' => 'nullable|string',
            'correo' => 'required|email|unique:usuario,correo',
            'telefono' => 'required|string',
            'gradoAcademico' => 'nullable|string',
            'RPE' => 'required|string|unique:usuario,RPE',
            'pass' => 'required|string|min:8',
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer|exists:tipoUsuario,idTipoUsuario',
            'procesosSupervisor' => 'array',
            'procesosSupervisor.*' => 'integer|exists:proceso,idProceso',
        ]);

        DB::beginTransaction();

        try {
            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellidoPat' => $validated['apellidoPat'],
                'apellidoMat' => $validated['apellidoMat'],
                'correo' => $validated['correo'],
                'telefono' => $validated['telefono'],
                'gradoAcademico' => $validated['gradoAcademico'],
                'RPE' => $validated['RPE'],
                'pass' => Hash::make($validated['pass']),
                'activo' => 1,
                'fechaRegistro' => now(),
            ]);

            $usuario->roles()->sync($validated['roles']);

            // Insertar en supervisor_proceso si tiene el rol de Supervisor
            $rolSupervisor = TipoUsuario::where('nombreRol', 'Supervisor')->first();

            if ($rolSupervisor && in_array($rolSupervisor->idTipoUsuario, $validated['roles'])) {
                if (!empty($validated['procesosSupervisor'])) {
                    foreach ($validated['procesosSupervisor'] as $idProceso) {
                        DB::table('supervisor_proceso')->insert([
                            'idUsuario' => $usuario->idUsuario,
                            'idProceso' => $idProceso
                        ]);
                    }
                }
                // Si no trae procesos, simplemente se crea el Supervisor sin asignación inicial
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario->load('roles')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function getSupervisores()
    {
        try {
            $supervisores = Usuario::whereHas('roles', function ($q) {
                $q->where('nombreRol', 'Supervisor');
            })->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat']);

            return response()->json([
                'success' => true,
                'data' => $supervisores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de supervisores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // App/Http/Controllers/Api/UsuarioController.php

    public function index()
    {
        $usuarios = Usuario::with(['roles'])->get();

        // Filtra líderes
        $leaders = $usuarios->filter(fn($u) => $u->roles->contains('nombreRol', 'Líder'));

        if ($leaders->isNotEmpty()) {
            $leaderIds = $leaders->pluck('idUsuario');

            // procesos de cada líder (1 proceso por líder)
            $procesos = DB::table('proceso')
                ->whereIn('idUsuario', $leaderIds)
                ->select('idProceso', 'idUsuario as idLider')
                ->get();

            $procIds = $procesos->pluck('idProceso');

            // supervisor asignado a cada proceso
            $sp = DB::table('supervisor_proceso')
                ->whereIn('idProceso', $procIds)
                ->select('idProceso', 'idUsuario as idSupervisor')
                ->get();

            $supervisorIds = $sp->pluck('idSupervisor')->unique()->values();

            $supervisores = Usuario::whereIn('idUsuario', $supervisorIds)
                ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat']);

            // Maps rápidos
            $procesoByLeader = $procesos->keyBy('idLider');          // idLider -> {idProceso,...}
            $supervisorByProceso = $sp->keyBy('idProceso');               // idProceso -> {idSupervisor,...}
            $supervisorUserById = $supervisores->keyBy('idUsuario');     // idSupervisor -> Usuario

            // Adjunta supervisor a cada líder
            foreach ($usuarios as $u) {
                if ($u->roles->contains('nombreRol', 'Líder')) {
                    $proc = $procesoByLeader->get($u->idUsuario);
                    $supId = $proc ? optional($supervisorByProceso->get($proc->idProceso))->idSupervisor : null;

                    if ($supId && $supervisorUserById->has($supId)) {
                        $sup = $supervisorUserById->get($supId);
                        // Campo ad-hoc "supervisor" para que el front lo reciba directo
                        $u->supervisor = [
                            'idUsuario' => $sup->idUsuario,
                            'nombre' => $sup->nombre,
                            'apellidoPat' => $sup->apellidoPat,
                            'apellidoMat' => $sup->apellidoMat,
                        ];
                    } else {
                        $u->supervisor = null;
                    }
                }
            }
        }

        return response()->json(['data' => $usuarios]);
    }


    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'apellidoPat' => 'sometimes|string',
            'apellidoMat' => 'nullable|string',
            'correo' => 'sometimes|email|unique:usuario,correo,' . $id . ',idUsuario',
            'telefono' => 'sometimes|string',
            'gradoAcademico' => 'nullable|string',
            'RPE' => 'sometimes|string|unique:usuario,RPE,' . $id . ',idUsuario',
            'pass' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:tipoUsuario,idTipoUsuario',
            'procesosAsignados' => 'nullable|array',
            'procesosAsignados.*' => 'integer|exists:proceso,idProceso',
        ]);

        DB::beginTransaction();

        try {
            // Si se incluye 'pass', encriptarla
            if (isset($validated['pass'])) {
                $validated['pass'] = Hash::make($validated['pass']);
            }
            unset($validated['roles'], $validated['procesosAsignados']);

            $usuario->update($validated);

            if (isset($validated['roles'])) {
                $usuario->roles()->sync($validated['roles']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $usuario->load(['roles', 'tipoPrincipal'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(null, 204);
    }

    public function getAuditores()
    {
        try {
            $rolAuditor = TipoUsuario::where('nombreRol', 'Auditor')->first();

            if (!$rolAuditor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol de Auditor no encontrado'
                ], 404);
            }

            $auditores = Usuario::where('idTipoUsuario', $rolAuditor->idTipoUsuario)
                ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat', 'correo', 'telefono', 'gradoAcademico']);

            return response()->json([
                'success' => true,
                'data' => $auditores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener auditores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAuditoresBasico()
    {
        try {
            $auditores = Usuario::where('idTipoUsuario', 2)
                ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat', 'correo', 'telefono', 'gradoAcademico']);

            return response()->json([
                'success' => true,
                'data' => $auditores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener auditores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProcesosPorAuditor($idUsuario)
    {
        try {
            $procesos = DB::table('proceso')
                ->where('idUsuario', $idUsuario)
                ->select('idProceso', 'nombreProceso', 'idEntidad', 'estado')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $procesos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener procesos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerNombreCompleto($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellidoPat . ' ' . $usuario->apellidoMat);

        return response()->json(['nombreCompleto' => $nombreCompleto]);
    }

    public function asignarProcesosSupervisor(Request $request, $idUsuario)
    {
        $validated = $request->validate([
            'procesos' => 'required|array|min:1',
            'procesos.*' => 'integer|exists:proceso,idProceso',
        ]);

        try {
            DB::beginTransaction();

            $procesos = $validated['procesos'];

            // 1) Limpiar todas las asignaciones del supervisor actual
            DB::table('supervisor_proceso')
                ->where('idUsuario', $idUsuario)
                ->delete();

            // 2) Asegurar 1 supervisor por proceso:
            //    si alguno de los procesos ya estaba asignado a otro supervisor, lo liberamos
            DB::table('supervisor_proceso')
                ->whereIn('idProceso', $procesos)
                ->delete();

            // 3) Insertar nuevas asignaciones
            $rows = [];
            foreach ($procesos as $idProceso) {
                $rows[] = [
                    'idUsuario' => $idUsuario,
                    'idProceso' => $idProceso,
                ];
            }
            DB::table('supervisor_proceso')->insert($rows);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procesos asignados al supervisor correctamente',
                'asignados' => $procesos,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar procesos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function procesosDeSupervisor($idUsuario)
    {
        $procesos = DB::table('supervisor_proceso as sp')
            ->join('proceso as p', 'sp.idProceso', '=', 'p.idProceso')
            ->select('p.idProceso', 'p.nombreProceso')
            ->where('sp.idUsuario', $idUsuario)
            ->get();

        return response()->json([
            'procesos' => $procesos,
            'procesosIds' => $procesos->pluck('idProceso')
        ]);
    }

}