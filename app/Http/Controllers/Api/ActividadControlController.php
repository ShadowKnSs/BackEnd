<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ActividadControl;
use App\Models\Registros;
use App\Models\AnalisisDatos;
use App\Models\NeceInter;
use App\Models\IndicadorConsolidado;

class ActividadControlController extends Controller
{
    // Obtener todas las actividades
    public function index($idProceso)
    {
        // Obtener las actividades de control asociadas al idProceso
        $actividades = ActividadControl::where('idProceso', $idProceso)->get();
        return response()->json($actividades, 200);
    }


    // Crear una nueva actividad
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1) Crear la ActividadControl
            $actividad = ActividadControl::create([
                'idProceso' => $request->get('idProceso'),
                'nombreActividad' => $request->get('nombreActividad'),
                'procedimiento' => $request->get('procedimiento'),
                'caracteristicasVerificar' => $request->get('caracteristicasVerificar'),
                'criterioAceptacion' => $request->get('criterioAceptacion'),
                'frecuencia' => $request->get('frecuencia'),
                'identificacionSalida' => $request->get('identificacionSalida'),
                'registroSalida' => $request->get('registroSalida'),
                'tratamiento' => $request->get('tratamiento'),
                'responsable' => $request->get('responsable'),
            ]);

            // 2) Obtener el REGISTRO correspondiente
            $registro = Registros::where('idProceso', $request->idProceso)
                ->where('año', $request->año)
                ->where('Apartado', 'Análisis de Datos')
                ->firstOrFail();

            // 3) Obtener el idAnalisisDatos correspondiente a ese registro
            $analisis = AnalisisDatos::where('idRegistro', $registro->idRegistro)->firstOrFail();

            // 4) Verificar que exista un NeceInter con seccion 'Conformidad'
            $nece = NeceInter::where('idAnalisisDatos', $analisis->idAnalisisDatos)
                ->where('seccion', 'Conformidad')
                ->firstOrFail();

            if (!$nece) {
                return response()->json([
                    'message' => 'No se encontró información en NeceInter con sección Conformidad para el análisis de datos indicado.',
                    'idAnalisisDatos' => $analisis->idAnalisisDatos
                ], 404);
            }
            // 5) Crear el indicador usando ese análisis
            $indicador = IndicadorConsolidado::create([
                'idRegistro' => $registro->idRegistro,
                'idProceso' => $actividad->idProceso,
                'nombreIndicador' => $actividad->nombreActividad,
                'origenIndicador' => 'ActividadControl',
                'periodicidad' => 'Semestral',
                'meta' => 100,
            ]);

            // 6) Guardar la FK en la actividad
            $actividad->idIndicador = $indicador->idIndicador;
            $actividad->save();

            DB::commit();

            return response()->json([
                'message' => 'ActividadControl y su indicador creados exitosamente.',
                'actividad' => $actividad,
                'indicador' => $indicador,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear ActividadControl e indicador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear la actividad y el indicador',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Mostrar una actividad específica
    public function show($id)
    {
        $actividad = ActividadControl::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        return response()->json($actividad, 200);
    }

    // Actualizar una actividad
    public function update(Request $request, $id)
    {
        $actividad = ActividadControl::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $request->validate([
            'idProceso' => 'integer',
            'idFormulario' => 'integer',
            'idResponsable' => 'integer',
            'nombreActividad' => 'string|max:255',
            'procedimiento' => 'string|max:255',
            'caracteristicasVerificar' => 'string',
            'criterioAceptacion' => 'string',
            'frecuencia' => 'string|max:255',
            'identificacionSalida' => 'string',
            'registroSalida' => 'string',
            'tratamiento' => 'string'
        ]);

        $actividad->update($request->all());
        return response()->json($actividad, 200);
    }

    // Eliminar una actividad
    public function destroy($id)
    {
        $actividad = ActividadControl::find($id);
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        $actividad->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
