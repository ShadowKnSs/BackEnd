<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntidadDependencia;
use App\Models\Proceso;

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

        // Verificar si ya existe una entidad con ese nombre (case-insensitive compatible con MySQL)
        $existeEntidad = EntidadDependencia::whereRaw('LOWER(nombreEntidad) = LOWER(?)', [$request->nombreEntidad])
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


    // Función para obtener los nombres de las entidades
    public function getNombres()
    {
        $nombres = EntidadDependencia::pluck('nombreEntidad');
        return response()->json(['nombres' => $nombres], 200);
    }

    public function toggleProcesos($id)
    {
        $entidad = EntidadDependencia::find($id);

        if (!$entidad) {
            return response()->json(['error' => 'Entidad no encontrada'], 404);
        }

        $idEntidad = $entidad->idEntidadDependencia;
        $procesos = Proceso::where('idEntidad', $idEntidad)->get();

        foreach ($procesos as $proceso) {
            // Si está en "Activo" lo cambia a "Inactivo", y viceversa
            $proceso->estado = ($proceso->estado === 'Activo') ? 'Inactivo' : 'Activo';
            $proceso->save();
        }


        return response()->json([
            'message' => 'Estado de los procesos actualizado',
            'procesos' => $procesos
        ], 200);
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
        \Log::info('📥 Petición a entidadesPorUsuario', $request->all());

        $idUsuario = (int) $request->input('idUsuario');
        $rolActivo = (string) $request->input('rolActivo');

        // Admin, Coordinador y Auditor: ven todas las entidades activas
        if (in_array($rolActivo, ['Admin', 'Coordinador de Calidad', 'Auditor'])) {
            $entidades = EntidadDependencia::select('idEntidadDependencia', 'nombreEntidad', 'icono', 'tipo')
                ->where('activo', 1)
                ->orderBy('nombreEntidad')
                ->get();
        }
        // Supervisor: SOLO entidades de procesos que supervisa
        elseif ($rolActivo === 'Supervisor') {
            $entidades = EntidadDependencia::select(
                'entidaddependencia.idEntidadDependencia',
                'entidaddependencia.nombreEntidad',
                'entidaddependencia.icono',
                'entidaddependencia.tipo'
            )
                ->whereIn('entidaddependencia.idEntidadDependencia', function ($q) use ($idUsuario) {
                    $q->select('proceso.idEntidad')
                        ->from('proceso')
                        ->join('supervisor_proceso', 'supervisor_proceso.idProceso', '=', 'proceso.idProceso')
                        ->where('supervisor_proceso.idUsuario', $idUsuario);
                })
                ->where('entidaddependencia.activo', 1)
                ->orderBy('entidaddependencia.nombreEntidad')
                ->get();
        }
        // Líder: sólo su entidad (los procesos donde él es dueño)
        elseif ($rolActivo === 'Líder') {
            $entidades = EntidadDependencia::select('idEntidadDependencia', 'nombreEntidad', 'icono', 'tipo')
                ->whereIn('idEntidadDependencia', function ($query) use ($idUsuario) {
                    $query->select('idEntidad')
                        ->from('proceso')
                        ->where('idUsuario', $idUsuario);
                })
                ->where('activo', 1)
                ->orderBy('nombreEntidad')
                ->get();
        } else {
            return response()->json(['message' => 'Sin permisos para ver entidades.'], 403);
        }

        return response()->json(['entidades' => $entidades], 200);
    }


    //actualizar una entidad/dependecia
    public function update(Request $request, $id)
    {
        $request->validate([
            'ubicacion' => 'sometimes|string|nullable',
            'nombreEntidad' => 'sometimes|string|nullable',
            'tipo' => 'sometimes|string|nullable',
            'icono' => 'sometimes|string|nullable',
            'activo' => 'sometimes|boolean',
        ]);

        $entidad = EntidadDependencia::find($id);

        if (!$entidad) {
            return response()->json(['error' => 'Entidad/dependencia no encontrada'], 404);
        }

        // Validación extra: solo verificar duplicados si se manda nombreEntidad
        if ($request->filled('nombreEntidad')) {
            $existeOtra = EntidadDependencia::whereRaw('LOWER(nombreEntidad) = ?', [strtolower($request->nombreEntidad)])
                ->where('idEntidadDependencia', '!=', $id)
                ->exists();
            if ($existeOtra) {
                return response()->json(['error' => 'Ya existe otra entidad/dependencia con ese nombre'], 409);
            }
        }

        // Actualizar solo los campos recibidos
        $entidad->update($request->all());

        return response()->json([
            'message' => 'Entidad/dependencia actualizada con éxito',
            'entidad' => $entidad
        ], 200);
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

    public function getNombres2()
    {
        $entidades = EntidadDependencia::select('idEntidad', 'nombre')->get();
        return response()->json(['nombres' => $entidades]);
    }


    public function obtenerProcesosPorNombreEntidad(Request $request)
    {
        $nombre = $request->query('nombre');

        $entidad = EntidadDependencia::with(['procesos:idProceso,nombreProceso,idEntidad'])
            ->where('nombreEntidad', $nombre)
            ->first();

        if (!$entidad) {
            return response()->json(['message' => 'Entidad no encontrada'], 404);
        }

        if ($entidad->procesos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron procesos para esta entidad'], 404);
        }

        return response()->json([
            'idEntidad' => $entidad->idEntidadDependencia,
            'procesos' => $entidad->procesos
        ]);
    }

}
