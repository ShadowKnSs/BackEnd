<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;

class LiderController extends Controller
{
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
    public function index2()
{
    // Obtener líderes que NO estén asignados en procesos
    $leaders = Usuario::with(['roles'])
        ->whereHas('roles', function ($q) {
            $q->where('nombreRol', 'Líder');
        })
        ->whereDoesntHave('procesos') // 👈 relación con procesos
        ->get();

    if ($leaders->isEmpty()) {
        return response()->json([
            'message' => 'No hay líderes disponibles'
        ], 200);
    }

    return response()->json(['leaders' => $leaders], 200);
}

}
