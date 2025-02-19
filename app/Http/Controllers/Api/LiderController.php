<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;

class LiderController extends Controller
{
    public function index(){
         $leaders = Usuario::whereHas('tipoUsuario', function ($query) {
            $query->where('nombreRol', 'Líder');
        })->get();

        return response()->json(['leaders' => $leaders], 200);
    }
}
