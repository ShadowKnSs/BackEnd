<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registros;
use App\Models\GestionRiesgos;
use App\Models\Riesgo;
use App\Models\Proceso;
use App\Models\EntidadDependencia;

class dataSemController extends Controller
{
    public function obtenerData(Request $request)
{
    $anio = $request->input('anio');
    $periodo = $request->input('periodo');

    if (!$anio || !$periodo) {
        return response()->json(['error' => 'Faltan parámetros anio o periodo'], 400);
    }

    \Log::info("Año recibido: $anio, Periodo recibido: $periodo");

    $periodo = explode('-', $periodo); 
    $mesInicio = intval(trim($periodo[0]));
    $mesFin = intval(trim($periodo[1]));

    \Log::info("Mes Inicio: $mesInicio, Mes Fin: $mesFin");

    // Obtener registros
    $registros = Registros::where('año', $anio)
        ->where('Apartado', 'Gestión Riesgo')
        ->get(['idRegistro', 'idProceso']);

    \Log::info("Registros obtenidos: " . json_encode($registros->toArray()));

    if ($registros->isEmpty()) {
        return response()->json([]);
    }

    $idRegistros = $registros->pluck('idRegistro');
    \Log::info("ID de Registros: " . json_encode($idRegistros));

    // Obtener Gestión de Riesgos
    $gestionRiesgos = GestionRiesgos::whereIn('idregistro', $idRegistros)->get(['idGesRies', 'idregistro']);
    \Log::info("Gestión de Riesgos obtenida: " . json_encode($gestionRiesgos->toArray()));

    if ($gestionRiesgos->isEmpty()) {
        return response()->json([]);
    }

    $idGesRies = $gestionRiesgos->pluck('idGesRies');
    \Log::info("ID de Gestión de Riesgos: " . json_encode($idGesRies));

    // Obtener riesgos filtrados por el periodo
    $riesgos = Riesgo::whereIn('idGesRies', $idGesRies)
        ->whereMonth('fechaImp', '>=', $mesInicio)
        ->whereMonth('fechaImp', '<=', $mesFin)
        ->get(['idGesRies', 'fuente', 'valorSeveridad', 'valorOcurrencia', 'valorNRP']);

    \Log::info("Riesgos obtenidos: " . json_encode($riesgos->toArray()));

    if ($riesgos->isEmpty()) {
        return response()->json([]);
    }

    $idGesRiesValidos = $riesgos->pluck('idGesRies');
    \Log::info("ID de Riesgos válidos: " . json_encode($idGesRiesValidos));

    $idRegistrosValidos = $gestionRiesgos->whereIn('idGesRies', $idGesRiesValidos)->pluck('idregistro');
    \Log::info("ID de Registros válidos: " . json_encode($idRegistrosValidos));

    $procesos = Proceso::whereIn('idProceso', $registros->whereIn('idRegistro', $idRegistrosValidos)->pluck('idProceso'))
        ->get(['idProceso', 'nombreProceso', 'idEntidad']);

    \Log::info("Procesos obtenidos: " . json_encode($procesos->toArray()));

    if ($procesos->isEmpty()) {
        return response()->json([]);
    }

    $entidades = EntidadDependencia::whereIn('idEntidadDependecia', $procesos->pluck('idEntidad'))
        ->get(['idEntidadDependecia', 'nombreEntidad']);

    \Log::info("Entidades obtenidas: " . json_encode($entidades->toArray()));

    // Mapear datos
    $entidadesMap = $entidades->keyBy('idEntidadDependecia');
    $procesosMap = $procesos->keyBy('idProceso');

    // Transformar resultados
    $resultado = $riesgos->map(function ($riesgo) use ($procesosMap, $gestionRiesgos, $registros, $entidadesMap) {
        $idGesRies = $riesgo->idGesRies;
        $idRegistro = optional($gestionRiesgos->firstWhere('idGesRies', $idGesRies))->idregistro;
        \Log::info("ID Registro encontrado: " . json_encode($idRegistro));

        if (!$idRegistro) {
            \Log::error("No se encontró ID Registro para IDGesRies: $idGesRies");
            return null;
        }

        $proceso = optional($procesosMap[$registros->firstWhere('idRegistro', $idRegistro)->idProceso] ?? null);
        \Log::info("Proceso encontrado: " . json_encode($proceso));

        if (!$proceso) {
            \Log::error("No se encontró Proceso para ID Registro: $idRegistro");
            return null;
        }

        $entidad = optional($entidadesMap[$proceso->idEntidad] ?? null);
        \Log::info("Entidad encontrada: " . json_encode($entidad));

        if (!$entidad) {
            \Log::error("No se encontró Entidad para ID Entidad: " . json_encode($proceso->idEntidad));
            return null;
        }

        return [
            'NombreProceso' => $proceso->nombreProceso,
            'Entidad' => $entidad->nombreEntidad,
            'valorSeveridad' => $riesgo->valorSeveridad,
            'valorOcurrencia' => $riesgo->valorOcurrencia,
            'valorNRP' => $riesgo->valorNRP,
            'fuente' => $riesgo->fuente,
        ];
    })->filter(); // Filtrar `null` en caso de errores

    \Log::info("Resultado Final: " . json_encode($resultado->toArray()));

    return response()->json($resultado);
}

}
