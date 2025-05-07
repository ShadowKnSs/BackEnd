<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\GestionRiesgos;
use App\Models\Registros;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\MacroProceso;

class GestionRiesgoController extends Controller
{
    /**
     * 1) Obtener datos generales (entidad, macroproceso, proceso) según el idRegistro.
     *    Supongamos que esta información está en la tabla 'registro' o en otra parte.
     *    Ajusta la lógica según tu base de datos real.
     *
     *    GET /api/gestionriesgos/{idRegistro}/datos-generales
     */
    public function getIdRegistro(Request $request)
    {
        Log::info("Consultando Id Registro");
        $request->validate([
            'idRegistro' => 'required|integer',
        ]);
            $registro = Registros::find($request->idRegistro);
            $proceso = Proceso::where('idProceso', $registro->idProceso)->first();
           
            Log::info("Consultando Id Registro: {$registro}");
            if (!$registro) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró registro de Análisis de Datos para el proceso y año especificados'
                ], 404);
            }
            $entidad = EntidadDependencia::where('idEntidadDependencia', $proceso->idEntidad)->first();
            $macroproceso = Macroproceso::where('idMacroproceso', $proceso->idMacroproceso)->first();
                

            return response()->json([
                'success' => true,
                'idRegistro' => $registro->idRegistro,
                'proceso'=> $proceso,
                'macro'=> $macroproceso->tipoMacroproceso,
                'entidad'=> $entidad->nombreEntidad
            ]);
        
    }

    public function getDatosGenerales($idRegistro)
    {
        // Ejemplo: si tu tabla 'registro' tiene columnas 'entidad', 'macroproceso', 'proceso', etc.
        $registro = Registros::find($idRegistro);
        if (!$registro) {
            return response()->json(['message' => 'No existe el registro especificado'], 404);
        }

        // Ajusta el shape del JSON según tu conveniencia
        $datos = [
            'entidad'       => $registro->entidad ?? '', 
            'macroproceso'  => $registro->macroproceso ?? '',
            'proceso'       => $registro->proceso ?? '',
            // ... cualquier otro campo que quieras exponer
        ];

        return response()->json($datos, 200);
    }

    /**
     * 2) Verificar si ya existe un registro en gestionriesgos asociado a un idRegistro.
     *    GET /api/gestionriesgos/{idRegistro}
     *    - Si no existe, retornar 404.
     *    - Si existe, retornar la fila.
     */
    public function showByRegistro($idRegistro)
    {
        // Buscar si hay una fila en gestionriesgos con ese idRegistro
        $gestion = GestionRiesgos::where('idRegistro', $idRegistro)->first();
        if (!$gestion) {
            return response()->json(['message' => 'No existe un registro en gestionriesgos para este idRegistro'], 404);
        }

        return response()->json($gestion, 200);
    }

    /**
     * 3) Crear un nuevo registro en la tabla gestionriesgos.
     *    POST /api/gestionriesgos
     *    - Esperamos en el body: { "idRegistro": X, "elaboro": "...", "fechaelaboracion": "YYYY-MM-DD" }
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validar los campos mínimos
            $data = $request->validate([
                'idRegistro'       => 'required|integer',
                'elaboro'          => 'nullable|string',
                'fechaelaboracion' => 'nullable|date',
            ]);

            // Crear el registro
            $gestion = GestionRiesgos::create($data);

            DB::commit();
            return response()->json($gestion, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al crear gestionriesgos: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear el registro en gestionriesgos',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 4) Actualizar un registro en gestionriesgos (ya existente).
     *    PUT /api/gestionriesgos/{idGesRies}
     *    - Body: { "elaboro": "...", "fechaelaboracion": "YYYY-MM-DD" }
     */
    public function update(Request $request, $idGesRies)
    {
        DB::beginTransaction();
        try {
            $gestion = GestionRiesgos::find($idGesRies);
            if (!$gestion) {
                return response()->json(['message' => 'No se encontró el registro en gestionriesgos'], 404);
            }

            // Validar datos que se pueden actualizar
            $data = $request->validate([
                'elaboro'          => 'nullable|string',
                'fechaelaboracion' => 'nullable|date',
            ]);
            // También podrías permitir cambiar el idRegistro si lo requieres

            $gestion->update($data);

            DB::commit();
            return response()->json($gestion, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al actualizar gestionriesgos: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el registro en gestionriesgos',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
