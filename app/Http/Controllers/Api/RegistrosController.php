<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Registros;
use App\Http\Controllers\Controller;

class RegistrosController extends Controller
{
    // Función para crear un nuevo registro
    public function store(Request $request)
{
    try {
        // Validar los datos
        $request->validate([
            'idProceso' => 'required|integer|exists:proceso,idProceso',
            'año' => 'required|integer',
        ]);

        // Crear un nuevo registro
        $registro = Registros::create([
            'idProceso' => $request->idProceso,
            'año' => $request->año,
        ]);

        return response()->json($registro, 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    // Función para obtener los registros de una determinado idProceso
    public function index($idProceso)
    {
        // Obtener todos los registros con el idProceso específico
        $registros = Registros::where('idProceso', $idProceso)->get();

        return response()->json($registros); // Retorna los registros en formato JSON
    }
}
