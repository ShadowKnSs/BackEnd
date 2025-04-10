<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\TipoUsuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'apellidoPat' => 'required|string',
            'apellidoMat' => 'nullable|string',
            'correo' => 'required|email|unique:usuario,correo',
            'telefono' => 'required|string',
            'gradoAcademico' => 'nullable|string',
            'RPE' => 'required|string|unique:usuario,RPE',
            'pass' => 'required|string|min:8',
            'idTipoUsuario' => 'required|integer',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellidoPat' => $validated['apellidoPat'],
            'apellidoMat' => $validated['apellidoMat'],
            'correo' => $validated['correo'],
            'telefono' => $validated['telefono'],
            'gradoAcademico' => $validated['gradoAcademico'],
            'RPE' => $validated['RPE'],
            'pass' => Hash::make($validated['pass']),
            'idTipoUsuario' => $validated['idTipoUsuario'],
            'activo' => 1,
            'fechaRegistro' => now(),
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    public function getSupervisores()
    {
        try {
            $rolSupervisor = TipoUsuario::where('nombreRol', 'Supervisor')->first();
            
            if (!$rolSupervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol de Supervisor no encontrado en la base de datos'
                ], 404);
            }

            $supervisores = Usuario::where('idTipoUsuario', $rolSupervisor->idTipoUsuario)
                ->get(['idUsuario', 'nombre', 'apellidoPat', 'apellidoMat']);

            return response()->json([
                'success' => true,
                'data' => $supervisores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de supervisores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $usuarios = Usuario::with('tipoUsuario')->get();

        return response()->json(['data' => $usuarios]);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        
        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'apellidoPat' => 'sometimes|string',
            'apellidoMat' => 'nullable|string',
            'correo' => 'sometimes|email|unique:usuario,correo,'.$id.',idUsuario',
            'telefono' => 'sometimes|string',
            'gradoAcademico' => 'nullable|string',
            'RPE' => 'sometimes|string|unique:usuario,RPE,'.$id.',idUsuario',
            'pass' => 'sometimes|string|min:8',
            'idTipoUsuario' => 'sometimes|integer|exists:tipousuario,idTipoUsuario',
            'idSupervisor' => 'nullable|integer|exists:usuario,idUsuario'
        ]);

        if (isset($validated['pass'])) {
            $validated['pass'] = Hash::make($validated['pass']);
        }

        $usuario->update($validated);
        
        $usuario->load('tipoUsuario');

        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(null, 204);
    }
}
