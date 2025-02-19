<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proceso; // Import the Process model
use Illuminate\Support\Facades\Log;
class ProcessController extends Controller
{
    public function store(Request $request)
    {
        try {
            $proceso = Proceso::create($request->all());

            // Log de creación exitosa
            Log::info('Proceso creado exitosamente', [
                'id' => $proceso->id,
                'nombre' => $proceso->nombre,
                'usuario' => auth()->user()->name ?? 'Sistema'
            ]);

            return response()->json([
                'message' => 'Proceso creado exitosamente',
                'proceso' => $proceso
            ], 201);
        } catch (\Exception $e) {
            // Log de error en la creación
            Log::error('Error al crear proceso', [
                'error' => $e->getMessage(),
                'datos' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error al crear el proceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
