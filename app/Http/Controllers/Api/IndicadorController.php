<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Indicador;

class IndicadorController extends Controller
{
    // GET /api/indicadores
    public function index()
    {
        $indicadores = Indicador::all();
        return response()->json(['indicadores' => $indicadores], 200);
    }

    // POST /api/indicadores
    public function store(Request $request)
    {
        // Validar datos básicos para el indicador
        $validated = $request->validate([
            'tipo' => 'required|in:Encuesta de Satisfacción,Retroalimentación,Evaluación de proveedores,Plan de control,Mapa de proceso,Gestión de Riesgos',
            'nombre' => 'required|string|max:255',
            'periodo' => 'required|in:Semestral,Anual',
            'meta' => 'nullable|string',
        ]);

        try {
            $indicador = Indicador::create($validated);
            // Si el indicador requiere registro en tablas adicionales, se puede hacer aquí.
            // Por ejemplo, si es Evaluación de proveedores, se espera que el request incluya campos adicionales:
            if ($indicador->tipo === "Evaluación de proveedores" && $request->has(['confiable', 'condicionado', 'no_confiable', 'periodoEvaluacion'])) {
                $indicador->evaluacionProveedor()->create([
                    'confiable' => $request->input('confiable'),
                    'condicionado' => $request->input('condicionado'),
                    'no_confiable' => $request->input('no_confiable'),
                    'periodo' => $request->input('periodoEvaluacion'),
                ]);
            }
            // De igual forma para Plan de control, Mapa de proceso, Gestión de Riesgos,
            // se pueden crear registros en sus respectivas tablas relacionadas.

            Log::info('Indicador creado', ['id' => $indicador->id, 'nombre' => $indicador->nombre]);
            return response()->json(['indicador' => $indicador, 'message' => 'Indicador creado exitosamente'], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear indicador', ['error' => $e->getMessage(), 'datos' => $request->all()]);
            return response()->json(['message' => 'Error al crear el indicador', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/indicadores/{id}
    public function show($id)
    {
        $indicador = Indicador::findOrFail($id);
        return response()->json(['indicador' => $indicador], 200);
    }

    // PUT/PATCH /api/indicadores/{id}
    public function update(Request $request, $id)
    {
        $indicador = Indicador::findOrFail($id);
        $validated = $request->validate([
            'tipo' => 'required|in:Encuesta de Satisfacción,Retroalimentación,Evaluación de proveedores,Plan de control,Mapa de proceso,Gestión de Riesgos',
            'nombre' => 'required|string|max:255',
            'periodo' => 'required|in:Semestral,Anual',
            'meta' => 'nullable|string',
        ]);

        try {
            $indicador->update($validated);
            // Actualiza también la información en las tablas relacionadas, si aplica.
            return response()->json(['indicador' => $indicador, 'message' => 'Indicador actualizado'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el indicador', 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/indicadores/{id}
    public function destroy($id)
    {
        $indicador = Indicador::findOrFail($id);
        try {
            $indicador->delete();
            return response()->json(['message' => 'Indicador eliminado'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el indicador', 'error' => $e->getMessage()], 500);
        }
    }
}
