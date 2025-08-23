<?php

namespace App\Http\Controllers\Api;
use App\Models\Usuario;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;


class TipoUsuarioController extends Controller
{
    public function index()
    {
        try {
            // cache 10 min
            $roles = Cache::remember(
                'roles_index_v1',
                600,
                fn() =>
                TipoUsuario::all(['idTipoUsuario', 'nombreRol', 'descripcion'])
            );

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSupervisores()
    {
        try {
            $supervisorRole = TipoUsuario::where('nombreRol', 'Supervisor')->first();

            if (!$supervisorRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol de Supervisor no encontrado'
                ], 404);
            }

            $supervisores = Usuario::where('idTipoUsuario', $supervisorRole->idTipoUsuario)
                ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat']);

            return response()->json([
                'success' => true,
                'data' => $supervisores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener supervisores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}