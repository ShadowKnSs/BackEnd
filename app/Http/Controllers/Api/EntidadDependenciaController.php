<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntidadDependencia;

class EntidadDependenciaController extends Controller
{
    public function index()
    {
        $entidades = EntidadDependencia::all();
        return response()->json(['entidades' => $entidades], 200);
    }

    public function index1()
    {
        $entidades = EntidadDependencia::select('idEntidadDependencia', 'nombreEntidad')
            ->orderBy('nombreEntidad')
            ->get();

        return response()->json(['entidades' => $entidades]);
    }

    // Función para obtener los nombres de las entidades
    public function getNombres()
    {
        $nombres = EntidadDependencia::pluck('nombreEntidad');
        return response()->json(['nombres' => $nombres], 200);
    }

    public function show($id)
    {
        $entidad = EntidadDependencia::find($id);

        if (!$entidad) {
            return response()->json(["error" => "Entidad no encontrada"], 404);
        }

        return response()->json(["nombreEntidad" => $entidad->nombreEntidad]);
    }

    public function entidadesPorUsuario(Request $request)
    {
        \Log::info('📥 Petición a entidadesPorUsuario');
        \Log::info('🔐 ID Usuario:', [$request->input('idUsuario')]);
        \Log::info('🎭 Rol Activo:', [$request->input('rolActivo')]);
        $idUsuario = $request->input('idUsuario');
        $rolActivo = $request->input('rolActivo');

        // Si es Admin u otro con acceso total
        if ($rolActivo === 'Admin' || $rolActivo === 'Coordinador') {
            $entidades = EntidadDependencia::select('idEntidadDependencia', 'nombreEntidad')
                ->orderBy('nombreEntidad')
                ->get();
        }
        // Si es Líder de Proceso, solo su entidad (desde proceso)
        elseif ($rolActivo === 'Líder de Proceso') {
            $entidades = EntidadDependencia::whereIn('idEntidadDependencia', function ($query) use ($idUsuario) {
                $query->select('idEntidad')
                    ->from('proceso')
                    ->where('idUsuario', $idUsuario);
            })->get();
        } else {
            // Si no tiene acceso, regresa vacío o 403
            return response()->json(['message' => 'Sin permisos para ver entidades.'], 403);
        }

        return response()->json(['entidades' => $entidades]);
    }



}
