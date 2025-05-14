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

        $usuario = Usuario::where('RPE', $request->rpe)->first();
        if (!$usuario || !Hash::check($request->password, $usuario->pass)) {
            // if (!$usuario || hash('sha256', $request->password) !== $usuario->pass) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $roles = DB::table('usuario_tipo as ut')
            ->join('tipousuario as t', 'ut.idTipoUsuario', '=', 't.idTipoUsuario')
            ->where('ut.idUsuario', $usuario->idUsuario)
            ->select('t.idTipoUsuario', 't.nombreRol')
            ->get();

        foreach ($roles as $rol) {
            $rol->permisos = DB::table('permiso')
                ->where('idTipoUser', $rol->idTipoUsuario)
                ->select('modulo', 'tipoAcceso')
                ->get();
        }

        return response()->json([
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }

}
