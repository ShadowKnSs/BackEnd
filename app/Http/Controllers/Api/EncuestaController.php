<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;

class EncuestaController extends Controller
{
    public function store(Request $request, $idIndicador)
    {
        // Se espera que el request contenga un objeto "result" con:
        // { malo: <valor>, regular: <valor>, excelenteBueno: <valor>, noEncuestas: <valor> }
        $data = $request->get('result');

        $encuesta = Encuesta::updateOrCreate(
            ['idIndicador' => $idIndicador],
            [
                'malo' => $data['malo'] ?? 0,
                'regular' => $data['regular'] ?? 0,
                'excelenteBueno' => $data['excelenteBueno'] ?? 0,
                'noEncuestas' => $data['noEncuestas'] ?? 0,
            ]
        );

        return response()->json([
            'message' => 'Encuesta registrada exitosamente',
            'encuesta' => $encuesta
        ], 200);
    }
}
