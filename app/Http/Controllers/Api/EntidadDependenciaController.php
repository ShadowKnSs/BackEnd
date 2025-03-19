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

    // Función para obtener los nombres de las entidades
    public function getNombres()
    {
        $nombres = EntidadDependencia::pluck('nombreEntidad'); 
        return response()->json(['nombres' => $nombres], 200);
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
