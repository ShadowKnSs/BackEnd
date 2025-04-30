<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\formAnalisisDatos;
use App\Models\AnalisisDatos;
use App\Models\IndicadorConsolidado;
use App\Models\Encuesta;
use App\Models\EvaluaProveedores;
use App\Models\Retroalimentacion;
use App\Models\NeceInter;
use App\Models\Registros;
use App\Models\Proceso;
use App\Models\EntidadDependencia;
use App\Models\MacroProceso;


class FormAnalisisDatosController extends Controller
{
    /**
     * Obtener un registro de FormAnalisisDatos junto con sus datos asociados.
     */
    public function getIdRegistro(Request $request)
    {
        Log::info("Consultando Id Registro");
        $request->validate([
            'idProceso' => 'required|integer',
            'anio' => 'required|integer|digits:4'
        ]);
            $proceso = Proceso::find($request->idProceso);
            // Buscar el registro con el apartado "Análisis de Datos"
            $registro = Registros::where('idProceso', $request->idProceso)
                ->where('año', $request->anio)
                ->where('Apartado', 'Análisis de Datos')
                ->first();
           
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

    public function show($registro)
    {
        
        Log::info("Registro: {$registro}");
        $formAnalisisDatos = formAnalisisDatos::where('idRegistro',$registro)->get();

        $indicadores = AnalisisDatos::where('idRegistro', $registro)->get();
        Log::info(": {$indicadores}");
        $indicador= IndicadorConsolidado::where('idRegistro', $registro)->get();
        Log::info(": {$indicador}");
        
        $idsIndicadoresConsolidados = $indicador->pluck('idIndicador')->toArray();
        Log::info("IDs de Indicadores Consolidados: " . implode(',', $idsIndicadoresConsolidados));
        
        $encuesta = Encuesta::whereIn('idIndicador', $idsIndicadoresConsolidados)->get();
        $evaluacion = EvaluaProveedores::whereIn('idIndicador', $idsIndicadoresConsolidados)->get();
        $retroalimentacion = Retroalimentacion::whereIn('idIndicador', $idsIndicadoresConsolidados)->get();
        Log::info(": {$encuesta}");
        Log::info(": {$evaluacion}");
        Log::info(": {$retroalimentacion}");


        

        return response()->json([
            'formAnalisisDatos' => $formAnalisisDatos,
            'analisisDatos'=>$indicadores,
            'indicador' => $indicador,
            'encuesta' => $encuesta,
            'evaluacion' => $evaluacion,
            'retroalimentacion' => $retroalimentacion,
        ]);
    }

    /**
     * Actualizar.
     */
    public function updateNecesidadInterpretacion(Request $request, $idProceso)
    {
        Log::info("Hola desde update");
         // Loguear todos los datos entrantes
        Log::info('Datos recibidos en actualizarCampo:', [
            'idRegistro' => $idProceso,
            'seccion' => $request->seccion,
            'campo' => $request->campo,
            'valor' => $request->valor,
        ]);
        $formAnalisisDatos = formAnalisisDatos::find($idProceso);

        $request->validate([
            'seccion' => 'required|string',
            'campo' => 'required|in:necesidad,interpretacion',
            'valor' => 'nullable|string',
        ]);

        $analisis = AnalisisDatos::where('idRegistro', $idProceso)
            ->where('seccion', $request->seccion)
            ->first();

        if (!$analisis) {
            Log::warning("Registro no encontrado para idRegi y seccion={$request->seccion}");
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $analisis->{$request->campo} = $request->valor;
        $analisis->save();

        Log::info('Campo actualizado correctamente.', ['registro' => $analisis]);

        return response()->json([
            'message' => 'Campo actualizado correctamente',
            'data' => $analisis
        ]);
        }
}
