<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\GestionRiesgos;
use App\Models\Registros;


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
        $request->validate([
            'idRegistro' => 'required|integer',
        ]);

        $registro = Registros::with('proceso.entidad', 'proceso.macroproceso')->find($request->idRegistro);

        if (!$registro || !$registro->proceso) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró registro o proceso asociado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'idRegistro' => $registro->idRegistro,
            'proceso' => $registro->proceso,
            'macro' => optional($registro->proceso->macroproceso)->tipoMacroproceso,
            'entidad' => optional($registro->proceso->entidad)->nombreEntidad
        ]);
    }

    /**
     * 2) Verificar si ya existe un registro en gestionriesgos asociado a un idRegistro.
     *    GET /api/gestionriesgos/{idRegistro}
     *    - Si no existe, retornar 404.
     *    - Si existe, retornar la fila.
     */
    public function showByRegistro($idRegistro)
    {
        $gestion = GestionRiesgos::where('idRegistro', $idRegistro)->first();

        if (!$gestion) {
            return response()->json(['message' => 'No existe un registro en gestionriesgos para este idRegistro'], 404);
        }

        $gestion->fechaelaboracion = optional($gestion->fechaelaboracion)->format('Y-m-d');

        return response()->json($gestion, 200);
    }


    /**
     * 3) Crear un nuevo registro en la tabla gestionriesgos.
     *    POST /api/gestionriesgos
     *    - Esperamos en el body: { "idRegistro": X, "elaboro": "...", "fechaelaboracion": "YYYY-MM-DD" }
     */
    protected function validarDatos(Request $request)
    {
        return $request->validate([
            'idRegistro' => 'required|integer',
            'elaboro' => 'nullable|string',
            'fechaelaboracion' => 'nullable|date',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);

        DB::beginTransaction();
        try {
            $gestion = GestionRiesgos::create($data);
            DB::commit();
            return response()->json($gestion, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al crear gestionriesgos: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear el registro en gestionriesgos',
                'error' => $e->getMessage(),
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
        $data = $this->validarDatos($request);

        DB::beginTransaction();
        try {
            $gestion = GestionRiesgos::findOrFail($idGesRies);
            $gestion->update($data);
            DB::commit();
            return response()->json($gestion, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al actualizar gestionriesgos: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el registro en gestionriesgos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
