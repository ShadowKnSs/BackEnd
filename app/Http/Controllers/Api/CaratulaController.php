<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Caratula;
use App\Services\ControlCambiosService;

class CaratulaController extends Controller
{
    public function show($idProceso)
    {
        $caratula = Caratula::where('idProceso', $idProceso)->first();

        if (!$caratula) {
            return response()->json(null);
        }

        return response()->json([
            'idCaratula' => $caratula->idCaratula,
            'idProceso' => $caratula->idProceso,
            'version' => $caratula->version,
            'responsableNombre' => $caratula->responsable_nombre,
            'responsableCargo' => $caratula->responsable_cargo,
            'revisoNombre' => $caratula->reviso_nombre,
            'revisoCargo' => $caratula->reviso_cargo,
            'aproboNombre' => $caratula->aprobo_nombre,
            'aproboCargo' => $caratula->aprobo_cargo,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idProceso' => 'required|integer',
            'version' => 'required|string',
            'responsable_nombre' => 'nullable|string',
            'responsable_cargo' => 'nullable|string',
            'reviso_nombre' => 'nullable|string',
            'reviso_cargo' => 'nullable|string',
            'aprobo_nombre' => 'nullable|string',
            'aprobo_cargo' => 'nullable|string',
        ]);

        $caratula = Caratula::updateOrCreate(
            ['idProceso' => $request->idProceso],
            $request->only([
                'version',
                'responsable_nombre',
                'responsable_cargo',
                'reviso_nombre',
                'reviso_cargo',
                'aprobo_nombre',
                'aprobo_cargo',
            ])
        );

        // Registrar cambio automático
        ControlCambiosService::registrarCambio(
            $request->idProceso,
            'Carátula',
            'creó',
            "Versión {$request->version} - Responsable: {$request->responsable_nombre}, Revisó: {$request->reviso_nombre}, Aprobó: {$request->aprobo_nombre}"
        );

        return response()->json([
            'idCaratula' => $caratula->idCaratula,
            'version' => $caratula->version,
            'message' => 'Carátula guardada correctamente',
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'version' => 'required|string', // Validar versión
            'responsable_nombre' => 'required|string|max:125',
            'responsable_cargo' => 'required|string|max:125',
            'reviso_nombre' => 'required|string|max:125',
            'reviso_cargo' => 'required|string|max:125',
            'aprobo_nombre' => 'required|string|max:125',
            'aprobo_cargo' => 'required|string|max:125',
        ]);

        $caratula = Caratula::findOrFail($id);

        // Capturar valores anteriores para el control de cambios
        $valoresAnteriores = [
            'version' => $caratula->version,
            'responsable_nombre' => $caratula->responsable_nombre,
            'responsable_cargo' => $caratula->responsable_cargo,
            'reviso_nombre' => $caratula->reviso_nombre,
            'reviso_cargo' => $caratula->reviso_cargo,
            'aprobo_nombre' => $caratula->aprobo_nombre,
            'aprobo_cargo' => $caratula->aprobo_cargo,
        ];

        $caratula->update([
            'version' => $request->version,
            'responsable_nombre' => $request->responsable_nombre,
            'responsable_cargo' => $request->responsable_cargo,
            'reviso_nombre' => $request->reviso_nombre,
            'reviso_cargo' => $request->reviso_cargo,
            'aprobo_nombre' => $request->aprobo_nombre,
            'aprobo_cargo' => $request->aprobo_cargo,
        ]);

        // Determinar qué campos cambiaron para el registro detallado
        $camposModificados = $this->obtenerCamposModificados($valoresAnteriores, $request->all());

        // Registrar cambio automático - ACTUALIZACIÓN DETALLADA
        if (!empty($camposModificados)) {
            ControlCambiosService::registrarCambio(
                $caratula->idProceso,
                'Carátula',
                'editó',
                "Versión {$request->version} - " . implode(', ', $camposModificados)
            );
        }

        return response()->json([
            'idCaratula' => $caratula->idCaratula,
            'version' => $caratula->version,
            'message' => 'Carátula actualizada correctamente'
        ]);
    }

    /**
     * Determina qué campos fueron modificados y genera mensajes descriptivos
     */
    private function obtenerCamposModificados($anteriores, $nuevos)
    {
        $camposModificados = [];

        // Verificar cambios en cada campo
        if ($anteriores['version'] !== $nuevos['version']) {
            $camposModificados[] = "Versión: {$anteriores['version']} → {$nuevos['version']}";
        }

        if ($anteriores['responsable_nombre'] !== $nuevos['responsable_nombre']) {
            $camposModificados[] = "Responsable: {$anteriores['responsable_nombre']} → {$nuevos['responsable_nombre']}";
        }

        if ($anteriores['responsable_cargo'] !== $nuevos['responsable_cargo']) {
            $camposModificados[] = "Cargo Responsable: {$anteriores['responsable_cargo']} → {$nuevos['responsable_cargo']}";
        }

        if ($anteriores['reviso_nombre'] !== $nuevos['reviso_nombre']) {
            $camposModificados[] = "Revisó: {$anteriores['reviso_nombre']} → {$nuevos['reviso_nombre']}";
        }

        if ($anteriores['reviso_cargo'] !== $nuevos['reviso_cargo']) {
            $camposModificados[] = "Cargo Revisó: {$anteriores['reviso_cargo']} → {$nuevos['reviso_cargo']}";
        }

        if ($anteriores['aprobo_nombre'] !== $nuevos['aprobo_nombre']) {
            $camposModificados[] = "Aprobó: {$anteriores['aprobo_nombre']} → {$nuevos['aprobo_nombre']}";
        }

        if ($anteriores['aprobo_cargo'] !== $nuevos['aprobo_cargo']) {
            $camposModificados[] = "Cargo Aprobó: {$anteriores['aprobo_cargo']} → {$nuevos['aprobo_cargo']}";
        }

        return $camposModificados;
    }
}
