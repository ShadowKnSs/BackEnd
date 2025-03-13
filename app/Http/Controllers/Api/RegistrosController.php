<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Registros;
use App\Http\Controllers\Controller;

class RegistrosController extends Controller
{
    /*// Función para obtener los registros de una determinado idProceso
    public function index($idProceso)
    {
        // Obtener todos los registros con el idProceso específico
        $registros = Registros::where('idProceso', $idProceso)->get();

        return response()->json($registros); // Retorna los registros en formato JSON
    }
    // Mostrar todos los registros por idProceso
    public function index(Request $request)
    {
        $registros = Registro::where('idProceso', $request->idProceso)->get();
        return response()->json($registros);
    }*/

    // Crear un nuevo registro
    public function store(Request $request)
{
    $request->validate([
        'idProceso' => 'required|integer|exists:proceso,idProceso',
        'año' => 'required|integer',
        'Apartado' => 'required|string',
    ]);

    // Verificar si ya existe un registro con el mismo idProceso, año y apartado
    $registroExistente = Registros::where('idProceso', $request->idProceso)
        ->where('año', $request->año)
        ->where('Apartado', $request->Apartado)
        ->first();

    if ($registroExistente) {
        return response()->json(['message' => 'La carpeta ya existe'], 409);
    }

    $registro = Registros::create($request->all());
    return response()->json($registro, 201);
}


    // Mostrar un solo registro
    public function show($id)
    {
        $registro = Registros::findOrFail($id);
        return response()->json($registro);
    }

    // Actualizar un registro
    public function update(Request $request, $id)
    {
        $registro = Registros::findOrFail($id);
        $registro->update($request->all());
        return response()->json($registro);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $registro = Registros::findOrFail($id);
        $registro->delete();
        return response()->json(['message' => 'Registro eliminado']);
    }
    public function obtenerRegistrosPorProcesoYApartado(Request $request)
    {
        $request->validate([
            'idProceso' => 'required|integer',
            'Apartado' => 'required|string',
        ]);

        $registros = Registros::where('idProceso', $request->idProceso)
            ->where('Apartado', $request->Apartado)
            ->get();

        return response()->json($registros);
    }

}
