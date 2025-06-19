<?php

namespace App\Services;

use App\Models\ControlCambio;
use Illuminate\Support\Carbon;

class ControlCambiosService
{
    public static function registrarCambio($idProceso, $seccion, $accion, $detalle = '')
    {
        $ultimaVersion = ControlCambio::where('idProceso', $idProceso)
            ->max('version') ?? -1;

        return ControlCambio::create([
            'idProceso' => $idProceso,
            'seccion' => $seccion,
            'edicion' => 5,
            'version' => $ultimaVersion + 1,
            'fechaRevision' => Carbon::now(),
            'descripcion' => "Se {$accion} información en el apartado {$seccion}" . ($detalle ? ": {$detalle}" : '.'),
        ]);
    }
}
