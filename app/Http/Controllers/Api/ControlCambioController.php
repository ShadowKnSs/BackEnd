<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ControlCambio;
use Illuminate\Http\Request;

class ControlCambioController extends Controller
{
    
    // Listar cambios por proceso
    public function porProceso($idProceso)
{
    return ControlCambio::where('idProceso', $idProceso)
        ->orderByDesc('fechaRevision')
        ->get();
}

}
