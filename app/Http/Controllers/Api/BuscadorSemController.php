<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuscadorSem;

class BuscadorSemController extends Controller
{
    public function buscarPorAnio(Request $request)
    {
        $anio = $request->input('anio');

        $reportes = BuscadorSem::where('anio', $anio)->get();

        if ($reportes->isEmpty()) {
            return response()->json(['message' => 'No se encontraron reportes para el año especificado.'], 404);
        }

        return response()->json($reportes);
    }
}