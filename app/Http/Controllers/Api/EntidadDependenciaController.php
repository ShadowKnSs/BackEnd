<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntidadDependencia;

class EntidadDependenciaController extends Controller
{
    public function index(){
        $entidades = EntidadDependencia::all();
        return response()->json(['entidades' => $entidades], 200);
    }
    public function show($id)
{
    $entidad = EntidadDependencia::find($id);

    if (!$entidad) {
        return response()->json(["error" => "Entidad no encontrada"], 404);
    }

    return response()->json(["nombreEntidad" => $entidad->nombreEntidad]);
}


  
}
