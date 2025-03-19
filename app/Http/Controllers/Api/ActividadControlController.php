<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ActividadControl;
use App\Models\IndicadorConsolidado;

class ActividadControlController extends Controller
{
    // Obtener todas las actividades
    public function index(Request $request)
    {
        // 1) Leer el query param "proceso" que te llega como ?proceso=XX
        $idProceso = $request->query('proceso');  // ó $request->get('proceso')
    
        // 2) Construir la query
        $query = ActividadControl::query();
    
        // 3) Si el usuario manda el param, filtramos
        if ($idProceso) {
            // Filtramos por idProceso
            $query->where('idProceso', $idProceso);
        }
    
        // 4) Obtenemos la colección resultante
        $actividades = $query->get();
    
        // 5) Devolvemos en JSON
        return response()->json($actividades, 200);
    }
    

    // Crear una nueva actividad
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1) Crear la ActividadControl
            // Ajusta los campos que recibes desde el front.
            // Ejemplo: name="nombreActividad", name="idProceso", etc.
            $actividad = ActividadControl::create([
                'idProceso'              => $request->get('idProceso'),
                'nombreActividad'        => $request->get('nombreActividad'),
                'procedimiento'          => $request->get('procedimiento'),
                'caracteriticasVerificar'=> $request->get('caracteriticasVerificar'),
                'criterioAceptacion'     => $request->get('criterioAceptacion'),
                'frecuencia'             => $request->get('frecuencia'),
                'identificacionSalida'   => $request->get('identificacionSalida'),
                'registroSalida'         => $request->get('registroSalida'),
                'tratamiento'            => $request->get('tratamiento'),
                'responsable'            => $request->get('responsable'),
            ]);

            // 2) Crear el IndicadorConsolidado asociado
            // Tomamos "nombreActividad" como "nombreIndicador"
            // idProceso, lo mismo
            // origenIndicador = "ActividadControl"
            // periodicidad = "Anual"
            // meta = null
            // idRegistro = null
            $indicador = IndicadorConsolidado::create([
                'idRegistro'       => null, // o lo que tú necesites
                'idProceso'        => $actividad->idProceso,
                'nombreIndicador'  => $actividad->nombreActividad,  // clave
                'origenIndicador'  => 'ActividadControl',
                'periodicidad'     => 'Semestral',
                'meta'             => null,
            ]);

            DB::commit();

            return response()->json([
                'message'   => 'ActividadControl y su indicador creados exitosamente.',
                'actividad' => $actividad,
                'indicador' => $indicador,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear ActividadControl e indicador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear la actividad y el indicador',
                'error'   => $e->getMessage()
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
