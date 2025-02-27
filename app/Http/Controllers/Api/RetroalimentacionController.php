<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Retroalimentacion;

class RetroalimentacionController extends Controller
{
    public function store(Request $request, $idIndicador)
    {
        $data = $request->get('result');
        $retro = Retroalimentacion::updateOrCreate(
            ['idIndicador' => $idIndicador],
            [
              'metodo' => $data['metodo'] ?? null,
              'cantidadFelicitacion' => $data['cantidadFelicitacion'] ?? 0,
              'cantidadSugerencia' => $data['cantidadSugerencia'] ?? 0,
              'cantidadQueja' => $data['cantidadQueja'] ?? 0,
            ]
        );
        return response()->json(['message' => 'Retroalimentación registrada', 'retro' => $retro], 200);
    }
}
