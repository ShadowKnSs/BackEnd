<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\TipoUsuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $allRoles = TipoUsuario::pluck('nombreRol', 'idTipoUsuario')->toArray();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidoPat' => 'required|string|max:255',
            'apellidoMat' => 'nullable|string|max:255',
            'correo' => 'required|email|unique:usuario,correo',
            'telefono' => 'required|string|max:10',
            'gradoAcademico' => 'nullable|string|max:100',
            'RPE' => 'required|string|max:20|unique:usuario,RPE',
            'pass' => 'required|string|min:8',
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer|exists:tipoUsuario,idTipoUsuario',
            'procesosSupervisor' => 'sometimes|array',
            'procesosSupervisor.*' => 'integer|exists:proceso,idProceso',
        ]);

        DB::beginTransaction();

        try {
            // Crear usuario sin consultas adicionales
            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellidoPat' => $validated['apellidoPat'],
                'apellidoMat' => $validated['apellidoMat'] ?? null,
                'correo' => $validated['correo'],
                'telefono' => $validated['telefono'],
                'gradoAcademico' => $validated['gradoAcademico'] ?? null,
                'RPE' => $validated['RPE'],
                'pass' => Hash::make($validated['pass']),
                'activo' => 1,
                'fechaRegistro' => now(),
            ]);

            // Sincronizar roles (más eficiente que attach individual)
            $usuario->roles()->sync($validated['roles']);

            // Verificar si es supervisor y procesar asignaciones
            $esSupervisor = false;
            foreach ($validated['roles'] as $roleId) {
                if (isset($allRoles[$roleId]) && $allRoles[$roleId] === 'Supervisor') {
                    $esSupervisor = true;
                    break;
                }
            }

            if ($esSupervisor && !empty($validated['procesosSupervisor'])) {
                // Insertar en lote para mejor rendimiento
                $procesosData = array_map(function ($idProceso) use ($usuario) {
                    return [
                        'idUsuario' => $usuario->idUsuario,
                        'idProceso' => $idProceso,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }, $validated['procesosSupervisor']);

                DB::table('supervisor_proceso')->insert($procesosData);
            }

            DB::commit();

            // Cargar relaciones necesarias para la respuesta
            $usuario->load('roles');

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
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

    // En el método index, optimizar la carga de relaciones
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 10), 100);
        $qParam = $request->query('q');
        $rol = $request->query('rol');
        $estado = $request->query('estado', 'all'); // all|true|false
        $excludeMe = filter_var($request->query('exclude_me', 'true'), FILTER_VALIDATE_BOOLEAN);

        // Modificar la query para incluir inactivos cuando sea necesario
        $query = Usuario::with(['roles', 'procesosSupervisados.proceso']);

        if ($estado === 'false') {
            // Necesitas quitar el Global Scope para poder ver inactivos
            $query->withInactive()->where('activo', 0);
        } elseif ($estado === 'true') {
            // No toques nada: el Global Scope ya filtra activo = 1
        }

        // Aplicar otros filtros
        if ($qParam) {
            $query->buscar($qParam);
        }

        if ($rol) {
            $query->filtrarRol($rol);
        }

        if ($excludeMe && Auth::check()) {
            $query->where('idUsuario', '!=', Auth::id());
        }

        $usuarios = $query->orderByDesc('fechaRegistro')->paginate($perPage);

        // Lógica optimizada para obtener supervisores
        $leaderIds = $usuarios->filter(function ($u) {
            return $u->roles->contains('nombreRol', 'Líder');
        })->pluck('idUsuario');

        if ($leaderIds->isNotEmpty()) {
            $procesosConSupervisores = DB::table('proceso as p')
                ->join('supervisor_proceso as sp', 'p.idProceso', '=', 'sp.idProceso')
                ->join('usuario as u', 'sp.idUsuario', '=', 'u.idUsuario')
                ->whereIn('p.idUsuario', $leaderIds)
                ->select('p.idUsuario as idLider', 'u.idUsuario', 'u.nombre', 'u.apellidoPat', 'u.apellidoMat')
                ->get()
                ->groupBy('idLider');

            foreach ($usuarios as $usuario) {
                if ($usuario->roles->contains('nombreRol', 'Líder') && isset($procesosConSupervisores[$usuario->idUsuario])) {
                    $supervisor = $procesosConSupervisores[$usuario->idUsuario]->first();
                    $usuario->supervisor = [
                        'idUsuario' => $supervisor->idUsuario,
                        'nombre' => $supervisor->nombre,
                        'apellidoPat' => $supervisor->apellidoPat,
                        'apellidoMat' => $supervisor->apellidoMat,
                    ];
                }
            }
        }

        return response()->json([
            'data' => $usuarios->items(),
            'pagination' => [
                'current_page' => $usuarios->currentPage(),
                'per_page' => $usuarios->perPage(),
                'total' => $usuarios->total(),
                'last_page' => $usuarios->lastPage(),
            ]
        ]);
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

            // Remover campos que no existen en la tabla usuario
            unset($validated['roles'], $validated['procesosAsignados']);

            $usuario->update($validated);

            // Sincronizar roles en la tabla pivote si se proporcionan
            if ($request->has('roles')) {
                $usuario->roles()->sync($request->roles);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $usuario->load(['roles']) 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // En UsuarioController.php, modificar el método destroy
    public function desactivar($id)
{
    try {
        $usuario = Usuario::findOrFail($id);
        
        // Verificar que no sea el usuario actual
        if (auth()->id() === $usuario->idUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivarte a ti mismo'
            ], 422);
        }

        // Verificar que no esté ya inactivo
        if (!$usuario->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario ya está inactivo'
            ], 422);
        }

        $usuario->update([
            'activo' => 0,
            'fecha_inactivacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario desactivado correctamente'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al desactivar el usuario',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    DB::beginTransaction();
    try {
        $usuario = Usuario::withInactive()->findOrFail($id);

        // Verificar que el usuario esté inactivo antes de eliminar
        if ($usuario->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar usuarios inactivos'
            ], 422);
        }

        // Verificar que no sea el usuario actual
        if (auth()->id() === $usuario->idUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo'
            ], 422);
        }

        // Eliminar relaciones en usuario_tipo
        DB::table('usuario_tipo')->where('idUsuario', $usuario->idUsuario)->delete();

        // Eliminar relaciones en supervisor_proceso
        DB::table('supervisor_proceso')->where('idUsuario', $usuario->idUsuario)->delete();

        // Finalmente eliminar el usuario
        $usuario->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado permanentemente'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el usuario',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function getAuditores()
    {
        try {
            $auditores = \DB::table('usuario as u')
                ->join('usuario_tipo as ut', 'ut.idUsuario', '=', 'u.idUsuario')
                ->where('ut.idTipoUsuario', 5)       // Auditor
                ->where('u.activo', 1)
                ->select(
                    'u.idUsuario',
                    'u.nombre',
                    'u.apellidoPat',
                    'u.apellidoMat',
                    'u.correo',
                    'u.telefono',
                    'u.gradoAcademico'
                )
                ->get();

            return response()->json(['success' => true, 'data' => $auditores]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener auditores', 'error' => $e->getMessage()], 500);
        }
    }



    public function getAuditoresBasico()
    {
        try {
            $auditores = \DB::table('usuario as u')
                ->join('usuario_tipo as ut', 'ut.idUsuario', '=', 'u.idUsuario')
                ->where('ut.idTipoUsuario', 2)       // Ajusta al rol “básico” que necesites
                ->where('u.activo', 1)
                ->select(
                    'u.idUsuario',
                    'u.nombre',
                    'u.apellidoPat',
                    'u.apellidoMat',
                    'u.correo',
                    'u.telefono',
                    'u.gradoAcademico'
                )
                ->get();

            return response()->json(['success' => true, 'data' => $auditores]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener auditores', 'error' => $e->getMessage()], 500);
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
        // 1. Validar entrada
        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            return response()->json(['procesos' => [], 'procesosIds' => []], 400);
        }

        // 2. Cache por usuario (más corto ya que es específico)
        $cacheKey = "procesos_supervisor_{$idUsuario}";
        $cacheTime = 300; // 5 minutos

        $result = Cache::remember($cacheKey, $cacheTime, function () use ($idUsuario) {
            $procesos = DB::table('supervisor_proceso as sp')
                ->join('proceso as p', 'sp.idProceso', '=', 'p.idProceso')
                ->select('p.idProceso', 'p.nombreProceso')
                ->where('sp.idUsuario', $idUsuario)
                // 3. Solo procesos activos
                ->where('p.estado', 'Activo')
                ->orderBy('p.nombreProceso')
                ->get();

            return [
                'procesos' => $procesos,
                'procesosIds' => $procesos->pluck('idProceso')
            ];
        });

        return response()->json($result);
    }

    public function cambiarEstado($id)
    {
        $usuario = Usuario::withInactive()->findOrFail($id);
        $usuario->update(['activo' => !$usuario->activo]);

        return response()->json([
            'message' => 'Estado actualizado',
            'activo' => $usuario->activo
        ]);
    }

    public function reactivar($id)
    {
        try {
            $usuario = Usuario::withInactive()->findOrFail($id);

            // Verificar si el usuario ya está activo
            if ($usuario->activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario ya está activo'
                ], 422);
            }

            // Verificar si el usuario ha estado inactivo por más de 1 año
            $fechaInactivacion = $usuario->fecha_inactivacion ?? $usuario->updated_at;
            $fechaLimite = now()->subYear();

            if ($fechaInactivacion && $fechaInactivacion->lt($fechaLimite)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede reactivar un usuario que ha estado inactivo por más de 1 año'
                ], 422);
            }

            // Reactivar el usuario
            $usuario->update([
                'activo' => 1,
                'fecha_inactivacion' => null // Limpiar fecha de inactivación
            ]);

            // Cargar relaciones para la respuesta
            $usuario->load(['roles', 'procesosSupervisados.proceso']);

            return response()->json([
                'success' => true,
                'message' => 'Usuario reactivado correctamente',
                'usuario' => $usuario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}