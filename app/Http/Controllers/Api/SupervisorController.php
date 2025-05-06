<?php

namespace App\Http\Controllers\Api;

use App\Models\SupervisorProceso;
use App\Models\Proceso;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function obtenerSupervisorPorProceso($idProceso)
{
    $asignacion = SupervisorProceso::with('usuario')
        ->where('idProceso', $idProceso)
        ->first(); // este es el SUPERVISOR asignado al proceso

    if (!$asignacion || !$asignacion->usuario) {
        return response()->json([
            'success' => false,
            'message' => 'No se encontró un supervisor asignado a este proceso.'
        ]);
    }

    $usuario = $asignacion->usuario; // Este sí es el SUPERVISOR

    return response()->json([
        'success' => true,
        'supervisor' => [
            'nombre' => $usuario->nombre . ' ' . $usuario->apellidoPat . ' ' . $usuario->apellidoMat,
            'correo' => $usuario->correo,
            'telefono' => $usuario->telefono,
            'gradoAcademico' => $usuario->gradoAcademico,
        ]
    ]);
}
    public function procesoPorLider(Request $request)
    {
        $idUsuario = $request->input('idUsuario');

        \Log::info('📥 Petición a procesoPorLider', ['idUsuario' => $idUsuario]);

        $proceso = Proceso::where('idUsuario', $idUsuario)->first();

        if (!$proceso) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un proceso asignado al usuario.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'idProceso' => $proceso->idProceso
        ]);
    }

}
