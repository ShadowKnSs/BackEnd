<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Caratula;

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
            'responsable_nombre' => 'required|string',
            'responsable_cargo' => 'required|string',
            'reviso_nombre' => 'required|string',
            'reviso_cargo' => 'required|string',
            'aprobo_nombre' => 'required|string',
            'aprobo_cargo' => 'required|string',
        ]);

        $caratula = Caratula::updateOrCreate(
            ['idProceso' => $request->idProceso],
            $request->only([
                'responsable_nombre',
                'responsable_cargo',
                'reviso_nombre',
                'reviso_cargo',
                'aprobo_nombre',
                'aprobo_cargo',
            ])
        );

        return response()->json($caratula);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'responsable_nombre' => 'required|string',
            'responsable_cargo' => 'required|string',
            'reviso_nombre' => 'required|string',
            'reviso_cargo' => 'required|string',
            'aprobo_nombre' => 'required|string',
            'aprobo_cargo' => 'required|string',
        ]);

        $caratula = Caratula::findOrFail($id);

        $caratula->update([
            'responsable_nombre' => $request->responsable_nombre,
            'responsable_cargo' => $request->responsable_cargo,
            'reviso_nombre' => $request->reviso_nombre,
            'reviso_cargo' => $request->reviso_cargo,
            'aprobo_nombre' => $request->aprobo_nombre,
            'aprobo_cargo' => $request->aprobo_cargo,
        ]);

        return response()->json($caratula);
    }

}
