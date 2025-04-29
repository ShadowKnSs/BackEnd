<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntidadDependencia;

class EntidadDependenciaController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'ubicacion' => 'required|string',
            'nombreEntidad' => 'required|string',
            'tipo' => 'required|string',
            'icono' => 'required|string',
        ]);

        // Verificar si ya existe una entidad con ese nombre (insensible a mayúsculas)
        $existeEntidad = EntidadDependencia::whereRaw('LOWER(nombreEntidad) = ?', [strtolower($request->nombreEntidad)])
            ->exists();

        if ($existeEntidad) {
            return response()->json(['error' => 'Ya existe una entidad/dependencia con ese nombre'], 409);
        }

        // Crear un nuevo registro
        $entidad = EntidadDependencia::create([
            'nombreEntidad' => $request->nombreEntidad,
            'ubicacion' => $request->ubicacion,
            'tipo' => $request->tipo,
            'icono' => $request->icono,
        ]);

        return response()->json([
            'message' => 'Entidad/dependencia registrada con éxito',
            'entidad' => $entidad
        ], 201);
    }

    //obtener todas las entidades/dependecias 
    public function index()
    {
        $entidades = EntidadDependencia::all();
        return response()->json(['entidades' => $entidades], 200);
    }

    //obtener todas las entidades/dependecias ordenadas por nombre 
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
        if ($rolActivo === 'Admin' || $rolActivo === 'Coordinador' || $rolActivo === 'Auditor') {
            $entidades = EntidadDependencia::select('idEntidadDependencia', 'nombreEntidad', 'icono')
                ->orderBy('nombreEntidad')
                ->get();
        }
        // Si es Líder de Proceso, solo su entidad (desde proceso)
        elseif ($rolActivo === 'Líder') {
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
    //actualizar una entidad/dependecia
    public function update(Request $request, $id)
    {
        // Validar los datos
        $request->validate([
            'ubicacion' => 'required|string',
            'nombreEntidad' => 'required|string',
            'tipo' => 'required|string',
            'icono' => 'required|string',
        ]);

        $entidad = EntidadDependencia::find($id);

        if (!$entidad) {
            return response()->json(['error' => 'Entidad/dependencia no encontrada'], 404);
        }

        // Verificar si hay otra entidad con el mismo nombre (exceptuando a sí misma)
        $existeOtra = EntidadDependencia::whereRaw('LOWER(nombreEntidad) = ?', [strtolower($request->nombreEntidad)])
            ->where('idEntidadDependencia', '!=', $id)
            ->exists();

        if ($existeOtra) {
            return response()->json(['error' => 'Ya existe otra entidad/dependencia con ese nombre'], 409);
        }

        // Actualizar los datos
        $entidad->update([
            'nombreEntidad' => $request->nombreEntidad,
            'ubicacion' => $request->ubicacion,
            'tipo' => $request->tipo,
            'icono' => $request->icono,
        ]);

        return response()->json(['message' => 'Entidad/dependencia actualizada con éxito', 'entidad' => $entidad], 200);
    }
    //eliminar una entidad/dependecia
    public function destroy($id)
    {
        $entidad = EntidadDependencia::find($id);

        if (!$entidad) {
            return response()->json(['error' => 'Entidad/dependencia no encontrada'], 404);
        }

        $entidad->delete();

        return response()->json(['message' => 'Entidad/dependencia eliminada con éxito'], 200);
    }




}
