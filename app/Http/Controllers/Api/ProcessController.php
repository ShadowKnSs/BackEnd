<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\Registros;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class ProcessController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Crear proceso
            $proceso = Proceso::create($request->all());

            // Año actual
            $año = now()->year;

            // Apartados de la estructura
            $apartados = [
                "Auditoría",
                "Seguimiento",
                "Acciones de Mejora",
                "Gestión de Riesgo",
                "Análisis de Datos"
            ];

            // Crear registros por cada apartado
            foreach ($apartados as $apartado) {
                Registros::create([
                    'idProceso' => $proceso->idProceso,
                    'año' => $año,
                    'Apartado' => $apartado
                ]);
            }

            DB::commit();

            // Log de éxito
            Log::info('Proceso creado exitosamente con registros asociados', [
                'idProceso' => $proceso->idProceso,
                'usuario' => auth()->user()->name ?? 'Sistema'
            ]);

            return response()->json([
                'message' => 'Proceso y registros creados exitosamente',
                'proceso' => $proceso
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log de error
            Log::error('Error al crear proceso o registros', [
                'error' => $e->getMessage(),
                'datos' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error al crear el proceso y registros',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        $procesos = Proceso::all();
        return response()->json(['procesos' => $procesos], 200);
    }

    public function show($id)
    {
        $proceso = Proceso::findOrFail($id);
        return response()->json(['proceso' => $proceso], 200);
    }

    public function update(Request $request, $id)
    {
        $proceso = Proceso::findOrFail($id);
        //Me falta la validacion
        $proceso->update($request->all());
        return response()->json(['proceso' => $proceso], 200);
    }

    public function destroy($id)
    {
        $proceso = Proceso::findOrFail($id);
        $proceso->delete();
        return response()->json(['proceso' => $proceso], 200);
    }

    // Obtener solo los nombres de los procesos
    public function getNombres()
    {
        $nombres = Proceso::pluck('nombreProceso');
        return response()->json(['procesos' => $nombres], 200);
    }


    public function obtenerProcesosPorEntidad($idEntidad)
    {
        // Obtener todos los procesos de la entidad específica
        $procesos = Proceso::where('idEntidad', $idEntidad)->get();

        if ($procesos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron procesos para esta entidad'], 404);
        }

        return response()->json($procesos);
    }

    public function obtenerProcesoPorUsuario($idUsuario)
    {
        $proceso = Proceso::where('idUsuario', $idUsuario)->first();
        return response()->json($proceso);
    }


    public function getInfoPorProceso($idProceso)
    {
        $proceso = Proceso::find($idProceso);

        if (!$proceso) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }

        $entidad = EntidadDependencia::find($proceso->idEntidad);

        if (!$entidad) {
            return response()->json(['error' => 'Entidad no encontrada'], 404);
        }

        return response()->json([
            'proceso' => $proceso->nombreProceso,
            'entidad' => $entidad->nombreEntidad,
        ]);
    }

}

