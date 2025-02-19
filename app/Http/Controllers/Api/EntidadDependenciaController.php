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
  
}
