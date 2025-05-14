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
            'roles.*' => 'integer|exists:tipousuario,idTipoUsuario',
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
                'idTipoUsuario' => 1,
                'activo' => 1,
                'fechaRegistro' => now(),
            ]);

            $usuario->roles()->sync($validated['roles']);

            if (!empty($validated['roles'])) {
                $usuario->update(['idTipoUsuario' => $validated['roles'][0]]);

            }


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
            $rolSupervisor = TipoUsuario::where('nombreRol', 'Supervisor')->first();

            if (!$rolSupervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol de Supervisor no encontrado en la base de datos'
                ], 404);
            }

            $supervisores = Usuario::where('idTipoUsuario', $rolSupervisor->idTipoUsuario)
                ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat']);

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

    public function index()
    {
        $usuarios = Usuario::with(['roles', 'tipoPrincipal'])->get();
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
            'roles.*' => 'integer|exists:tipousuario,idTipoUsuario',
            'procesosAsignados' => 'nullable|array',
            'procesosAsignados.*' => 'integer|exists:proceso,idProceso',
        ]);

        DB::beginTransaction();

        try {
            // Si se incluye 'pass', encriptarla
            if (isset($validated['pass'])) {
                $validated['pass'] = Hash::make($validated['pass']);
            }

            $usuario->update($validated);

            if (isset($validated['roles'])) {
                $usuario->roles()->sync($validated['roles']);

                if (count($validated['roles'])) {
                    $usuario->update(['idTipoUsuario' => $validated['roles'][0]]);
                }
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


}