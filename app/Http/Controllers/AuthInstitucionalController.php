<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario; // Tu tabla de usuarios
use Illuminate\Support\Facades\Session;

class AuthInstitucionalController extends Controller
{
    public function handleCallback(Request $request)
    {
        $usuario = $request->get('usuario');
        $tipo = $request->get('tipo');

        if (!$usuario || !$tipo) {
            return redirect('/login')->with('error', 'Error en autenticación institucional');
        }

        // Busca el usuario en tu BD
        $user = Usuario::where('RPE', $usuario)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Usuario no autorizado');
        }

        // Guarda en sesión o emite token
        Session::put('usuario_id', $user->idUsuario);

        // Redirige al frontend
        return redirect("http://localhost:3000/auth-institucional?user={$usuario}&type={$tipo}");
    }
}


