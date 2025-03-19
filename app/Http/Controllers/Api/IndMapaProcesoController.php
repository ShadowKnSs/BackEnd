<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\IndMapaProceso;
use App\Models\IndicadorConsolidado;

class IndMapaProcesoController extends Controller
{
    // 1) Listar todos
    public function index(Request $request)
{
    // Verificamos si llega el query param ?proceso=XX
    $idProceso = $request->query('proceso');

    if ($idProceso) {
        // Filtramos por el idProceso
        $lista = IndMapaProceso::where('idProceso', $idProceso)->get();
    } else {
        // Si no llega nada, devolvemos todo
        $lista = IndMapaProceso::all();
    }

    return response()->json($lista, 200);
}


    // 2) Crear un nuevo registro
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validamos lo que venga
            $data = $request->validate([
                'idProceso'   => 'required|integer',   // si es obligatorio
                'descripcion' => 'required|string',
                'formula'     => 'required|string',
                'periodoMed'  => 'required|string',
                'responsable' => 'nullable|string',
                'meta'        => 'nullable|integer',
            ]);

            // 1) Crear el registro en IndMapaProceso
            $indMapa = IndMapaProceso::create([
                'idProceso'   => $data['idProceso'],
                'descripcion' => $data['descripcion'],
                'formula'     => $data['formula'],
                'periodoMed'  => $data['periodoMed'],
                'responsable' => $data['responsable'] ?? null,
                'meta'        => $data['meta'] ?? null,
            ]);

            // 2) Crear IndicadorConsolidado asociado
            $indicador = IndicadorConsolidado::create([
                'idRegistro'       => null,                     // o lo que necesites
                'idProceso'        => $data['idProceso'],       // si es la misma
                'nombreIndicador'  => $data['descripcion'],     // ej: la desc del IndMapa
                'origenIndicador'  => 'MapaProceso',            // fijo
                'periodicidad'     => $data['periodoMed'],      // parseado
                'meta'             => $data['meta'] ?? null,
            ]);


            DB::commit();

            return response()->json([
                'message'         => 'IndMapaProceso y su indicador creados exitosamente.',
                'indMapaProceso'  => $indMapa,
                'indicador'       => $indicador,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear IndMapaProceso e indicador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear el IndMapaProceso y el indicador',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 3) Mostrar un registro
    public function show($id)
    {
        $registro = IndMapaProceso::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($registro, 200);
    }

    // 4) Actualizar un registro
    public function update(Request $request, $id)
    {
        $registro = IndMapaProceso::find($id);
        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            // Validar
            $data = $request->validate([
                'idProceso'   => 'nullable|integer',
                'descripcion' => 'nullable|string',
                'formula'     => 'nullable|string',
                'periodoMed'  => 'nullable|string',
                'responsable' => 'nullable|string',
                'meta'        => 'nullable|integer',
            ]);

            // Actualizamos IndMapaProceso
            $registro->update($data);

            // Si existe un indicador consolidado asociado
            if ($registro->idIndicadorConsolidado) {
                $indicador = IndicadorConsolidado::find($registro->idIndicadorConsolidado);
                if ($indicador) {
                    // Sincronizamos datos
                    if (isset($data['idProceso'])) {
                        $indicador->idProceso = $data['idProceso'];
                    }
                    if (isset($data['descripcion'])) {
                        $indicador->nombreIndicador = $data['descripcion'];
                    }
                    if (isset($data['periodoMed'])) {
                        $indicador->periodicidad = $data['periodoMed'];
                    }
                    if (isset($data['meta'])) {
                        $indicador->meta = $data['meta'];
                    }
                    $indicador->save();
                }
            }

            DB::commit();
            return response()->json($registro, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar IndMapaProceso e indicador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el registro',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 5) Eliminar un registro
    public function destroy($id)
    {
        $registro = IndMapaProceso::find($id);
        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            // Si existe un indicador asociado, lo eliminamos
            if ($registro->idIndicadorConsolidado) {
                $indicador = IndicadorConsolidado::find($registro->idIndicadorConsolidado);
                if ($indicador) {
                    $indicador->delete();
                }
            }

            // Luego eliminamos el indMapaProceso
            $registro->delete();

            DB::commit();
            return response()->json(['message' => 'Registro eliminado'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar IndMapaProceso e indicador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el registro',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
