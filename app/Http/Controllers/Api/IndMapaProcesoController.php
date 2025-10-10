<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\IndMapaProceso;
use App\Models\Registros;
use App\Models\IndicadorConsolidado;
use App\Services\ControlCambiosService;

class IndMapaProcesoController extends Controller
{
    protected function validarRequest(Request $request, $required = true)
    {
        return $request->validate([
            'idProceso' => ($required ? 'required' : 'nullable') . '|integer',
            'descripcion' => ($required ? 'required' : 'nullable') . '|string',
            'formula' => ($required ? 'required' : 'nullable') . '|string',
            'periodoMed' => ($required ? 'required' : 'nullable') . '|string',
            'responsable' => 'nullable|string',
            'meta' => 'nullable|integer',
        ]);
    }

    public function index(Request $request)
    {
        $idProceso = $request->query('proceso');
        $lista = $idProceso
            ? IndMapaProceso::where('idProceso', $idProceso)->get()
            : IndMapaProceso::all();

        return response()->json($lista, 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->validarRequest($request);

            // Crear el nuevo registro de IndMapaProceso
            $indMapa = IndMapaProceso::create($data);

            // Obtener registros del proceso (tipo: Análisis de Datos)
            $registros = Registros::where('idProceso', $data['idProceso'])
                ->where('Apartado', 'Análisis de Datos')
                ->get();

            // Verificamos qué idRegistro ya tienen indicadores exactamente iguales
            $idRegistros = $registros->pluck('idRegistro');

            $existentes = IndicadorConsolidado::whereIn('idRegistro', $idRegistros)
                ->where('nombreIndicador', $data['descripcion'])
                ->where('origenIndicador', 'MapaProceso')
                ->where('periodicidad', $data['periodoMed'])
                ->where('meta', $data['meta'] ?? 100)
                ->pluck('idRegistro')
                ->toArray();

            // Solo crear indicadores donde no existan duplicados exactos
            foreach ($registros as $registro) {
                if (!in_array($registro->idRegistro, $existentes)) {
                    IndicadorConsolidado::create([
                        'idRegistro' => $registro->idRegistro,
                        'idProceso' => $data['idProceso'],
                        'nombreIndicador' => $data['descripcion'],
                        'origenIndicador' => 'MapaProceso',
                        'periodicidad' => $data['periodoMed'],
                        'meta' => $data['meta'] ?? 100,
                    ]);
                }
            }

            // Registrar cambio
            ControlCambiosService::registrarCambio(
                $data['idProceso'],
                'Mapa de Proceso',
                'agregó',
                'Indicador: ' . $data['descripcion']
            );

            DB::commit();
            return response()->json([
                'message' => 'IndMapaProceso y sus indicadores consolidados creados correctamente.',
                'indMapaProceso' => $indMapa,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el IndMapaProceso y los indicadores.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $registro = IndMapaProceso::find($id);
        if (!$registro)
            return response()->json(['message' => 'Registro no encontrado'], 404);

        DB::beginTransaction();
        try {
            $data = $this->validarRequest($request, false);
            $cambios = false;

            foreach ($data as $key => $value) {
                if ($registro->{$key} !== $value) {
                    $registro->{$key} = $value;
                    $cambios = true;
                }
            }

            if ($cambios) {
                $registro->save();

                if ($registro->idIndicadorConsolidado) {
                    $indicador = IndicadorConsolidado::find($registro->idIndicadorConsolidado);
                    if ($indicador) {
                        if (isset($data['idProceso']))
                            $indicador->idProceso = $data['idProceso'];
                        if (isset($data['descripcion']))
                            $indicador->nombreIndicador = $data['descripcion'];
                        if (isset($data['periodoMed']))
                            $indicador->periodicidad = $data['periodoMed'];
                        if (isset($data['meta']))
                            $indicador->meta = $data['meta'];
                        $indicador->save();
                    }
                }

                ControlCambiosService::registrarCambio(
                    $registro->idProceso,
                    'Mapa de Proceso',
                    'editó',
                    'Indicador: ' . ($data['descripcion'] ?? $registro->descripcion)
                );
            }

            DB::commit();
            return response()->json($registro, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $registro = IndMapaProceso::find($id);
        if (!$registro)
            return response()->json(['message' => 'Registro no encontrado'], 404);

        DB::beginTransaction();
        try {
            if ($registro->idIndicadorConsolidado) {
                $indicador = IndicadorConsolidado::find($registro->idIndicadorConsolidado);
                if ($indicador)
                    $indicador->delete();
            }

            $idProceso = $registro->idProceso;
            $descripcion = $registro->descripcion;
            $registro->delete();

            ControlCambiosService::registrarCambio(
                $idProceso,
                'Mapa de Proceso',
                'eliminó',
                'Indicador: ' . $descripcion
            );

            DB::commit();
            return response()->json(['message' => 'Registro eliminado'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
