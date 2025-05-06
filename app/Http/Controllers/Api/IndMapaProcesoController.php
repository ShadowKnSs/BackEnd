<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\IndMapaProceso;
use App\Models\Registros;
use App\Models\AnalisisDatos;
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
            $data = $request->validate([
                'idProceso' => 'required|integer',
                'descripcion' => 'required|string',
                'formula' => 'required|string',
                'periodoMed' => 'required|string',
                'responsable' => 'nullable|string',
                'meta' => 'nullable|integer',
            ]);

            // 1️⃣ Crear registro en IndMapaProceso
            $indMapa = IndMapaProceso::create([
                'idProceso' => $data['idProceso'],
                'descripcion' => $data['descripcion'],
                'formula' => $data['formula'],
                'periodoMed' => $data['periodoMed'],
                'responsable' => $data['responsable'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);

            // 2️⃣ Buscar todos los REGISTROS de tipo "Análisis de Datos" del proceso
            $registros = Registros::where('idProceso', $data['idProceso'])
                ->where('Apartado', 'Análisis de Datos')
                ->get();

            foreach ($registros as $registro) {
                $idRegistro = $registro->idRegistro;

                // ⚠️ Validar si ya existe el indicador en este registro
                $existeIndicador = IndicadorConsolidado::where('idRegistro', $idRegistro)
                    ->where('nombreIndicador', $data['descripcion'])
                    ->where('origenIndicador', 'MapaProceso')
                    ->exists();

                if (!$existeIndicador) {
                    IndicadorConsolidado::create([
                        'idRegistro' => $idRegistro,
                        'idProceso' => $data['idProceso'],
                        'nombreIndicador' => $data['descripcion'],
                        'origenIndicador' => 'MapaProceso',
                        'periodicidad' => $data['periodoMed'],
                        'meta' => $data['meta'] ?? 100, // Si meta viene nula, la ponemos en 100
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'IndMapaProceso y sus indicadores consolidados creados correctamente.',
                'indMapaProceso' => $indMapa,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Error al crear IndMapaProceso e indicadores:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al crear el IndMapaProceso y los indicadores.',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'idProceso' => 'nullable|integer',
                'descripcion' => 'nullable|string',
                'formula' => 'nullable|string',
                'periodoMed' => 'nullable|string',
                'responsable' => 'nullable|string',
                'meta' => 'nullable|integer',
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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
