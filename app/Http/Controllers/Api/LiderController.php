<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;

class LiderController extends Controller
{
    /*public function index() {
        $leaders = Usuario::where('idTipoUsuario', 4)->get();
    
        return response()->json(['leaders' => $leaders], 200);
    }*/
    public function index()
    {
        // Requiere que en el modelo Usuario exista:
        // public function roles() { return $this->belongsToMany(TipoUsuario::class, 'usuario_tipo', 'idUsuario', 'idTipoUsuario'); }
        $leaders = Usuario::with(['roles'])   // opcional: para ver sus roles
            ->whereHas('roles', function ($q) {
                $q->where('nombreRol', 'Líder');      // o ->where('idTipoUsuario', 2) si prefieres por id
            })
            ->get();

        return response()->json(['leaders' => $leaders], 200);
    }
    
}
