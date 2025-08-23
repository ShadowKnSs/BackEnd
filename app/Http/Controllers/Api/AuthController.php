<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'rpe' => 'required|string',
            'password' => 'required|string',
        ]);

        // Buscar usuario incluyendo inactivos para verificar estado
        $usuario = Usuario::withoutGlobalScope('active')
            ->where('RPE', $request->rpe)
            ->first();

        // Verificar existencia y estado activo
        if (!$usuario || $usuario->activo != 1) {
            return response()->json(['message' => 'Credenciales inválidas o cuenta desactivada'], 401);
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $usuario->pass)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Obtener roles y permisos en una sola consulta optimizada
        $roles = DB::table('usuario_tipo as ut')
            ->join('tipoUsuario as t', 'ut.idTipoUsuario', '=', 't.idTipoUsuario')
            ->leftJoin('permiso as p', 't.idTipoUsuario', '=', 'p.idTipoUser')
            ->where('ut.idUsuario', $usuario->idUsuario)
            ->select(
                't.idTipoUsuario', 
                't.nombreRol', 
                'p.modulo', 
                'p.tipoAcceso'
            )
            ->get();

        // Agrupar permisos por rol
        $groupedRoles = $roles->groupBy('idTipoUsuario')->map(function ($group) {
            $first = $group->first();
            return [
                'idTipoUsuario' => $first->idTipoUsuario,
                'nombreRol' => $first->nombreRol,
                'permisos' => $group->where('modulo', '!=', null)
                    ->map(function ($item) {
                        return [
                            'modulo' => $item->modulo,
                            'tipoAcceso' => $item->tipoAcceso
                        ];
                    })->unique()->values()
            ];
        })->values();

        return response()->json([
            'usuario' => $usuario,
            'roles' => $groupedRoles
        ]);
    }
}