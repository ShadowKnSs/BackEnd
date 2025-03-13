<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Riesgo;

class RiesgoController extends Controller
{
    /**
     * Muestra una lista de los riesgos.
     */
    public function index()
    {
        $riesgos = Riesgo::all();
        return response()->json($riesgos);
    }

    /**
     * Almacena un nuevo riesgo en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idGesRies' => 'required|integer',
            'responsable' => 'required|string|max:255',
            'fuente' => 'nullable|string|max:255',
            'tipoRiesgo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'consecuencias' => 'nullable|string',
            'valorSeveridad' => 'required|integer|min:1|max:10',
            'valorOcurrencia' => 'required|integer|min:1|max:10',
            'valorNRP' => 'required|integer',
            'actividades' => 'nullable|string',
            'accionMejora' => 'nullable|string',
            'fechaImp' => 'nullable|date',
            'fechaEva' => 'nullable|date',
            'reevaluacionSeveridad' => 'nullable|integer|min:1|max:10',
            'reevaluacionOcurencia' => 'nullable|integer|min:1|max:10',
            'reevaluacionNRP' => 'nullable|integer',
            'reevaluacionEfectividad' => 'nullable|integer|min:1|max:10',
            'analisisEfectividad' => 'nullable|string'
        ]);

        $riesgo = Riesgo::create($request->all());
        return response()->json($riesgo, 201);
    }

    /**
     * Muestra un riesgo específico.
     */
    public function show($id)
    {
        $riesgo = Riesgo::find($id);
        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado'], 404);
        }
        return response()->json($riesgo);
    }

    /**
     * Actualiza un riesgo existente.
     */
    public function update(Request $request, $id)
    {
        $riesgo = Riesgo::find($id);
        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado'], 404);
        }

        $riesgo->update($request->all());
        return response()->json($riesgo);
    }

    /**
     * Elimina un riesgo de la base de datos.
     */
    public function destroy($id)
    {
        $riesgo = Riesgo::find($id);
        if (!$riesgo) {
            return response()->json(['message' => 'Riesgo no encontrado'], 404);
        }

        $riesgo->delete();
        return response()->json(['message' => 'Riesgo eliminado correctamente']);
    }
}
