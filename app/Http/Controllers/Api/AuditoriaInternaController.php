<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditoriaInterna;
use App\Models\EquipoAuditor;
use App\Models\PersonalAuditado;
use App\Models\VerificacionRuta;
use App\Models\PuntosMejora;
use App\Models\CriteriosAuditoria;
use App\Models\Plazo;
use Illuminate\Support\Facades\DB;

class AuditoriaInternaController extends Controller
{
    // Obtener todas las auditorías con relaciones
    public function index()
    {
        $auditorias = AuditoriaInterna::with([
            'equipoAuditor',
            'personalAuditado',
            'verificacionRuta',
            'puntosMejora'
        ])->get();

        return response()->json($auditorias);
    }

    // Obtener una auditoría por ID
    public function show($id)
    {
        $auditoria = AuditoriaInterna::with([
            'equipoAuditor',
            'personalAuditado',
            'verificacionRuta',
            'puntosMejora'
        ])->find($id);

        if (!$auditoria) {
            return response()->json(['message' => 'Auditoría no encontrada'], 404);
        }

        return response()->json($auditoria);
    }

    // Crear una nueva auditoría
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->all();

            $auditoria = AuditoriaInterna::create($data);

            // Insertar equipo auditor
            if (!empty($data['equipoAuditor'])) {
                foreach ($data['equipoAuditor'] as $auditor) {
                    $auditor['idAuditorialInterna'] = $auditoria->idAuditorialInterna;
                    EquipoAuditor::create($auditor);
                }
            }

            // Insertar personal auditado
            if (!empty($data['personalAuditado'])) {
                foreach ($data['personalAuditado'] as $persona) {
                    $persona['idAuditorialInterna'] = $auditoria->idAuditorialInterna;
                    PersonalAuditado::create($persona);
                }
            }

            // Insertar verificaciones
            if (!empty($data['verificacionRuta'])) {
                foreach ($data['verificacionRuta'] as $verificacion) {
                    $verificacion['idAuditorialInterna'] = $auditoria->idAuditorialInterna;
                    VerificacionRuta::create($verificacion);
                }
            }

            // Insertar puntos de mejora
            if (!empty($data['puntosMejora'])) {
                foreach ($data['puntosMejora'] as $punto) {
                    $punto['idAuditorialInterna'] = $auditoria->idAuditorialInterna;
                    PuntosMejora::create($punto);
                }
            }

            // Insertar criterios
            if (!empty($data['criterios'])) {
                foreach ($data['criterios'] as $criterio) {
                    CriteriosAuditoria::create([
                        'idAuditorialInterna' => $auditoria->idAuditorialInterna,
                        'criterio' => $criterio
                    ]);
                }
            }

            // Insertar conclusiones
            if (isset($request->conclusiones) && is_array($request->conclusiones)) {
                foreach ($request->conclusiones as $conclusion) {
                    \App\Models\ConclusionesGenerales::create([
                        'idAuditoriaInterna' => $auditoria->idAuditorialInterna,
                        'nombre' => $conclusion['nombre'],
                        'descripcionConclusion' => $conclusion['observaciones']
                    ]);
                }
            }

            // Insertar plazos
            if (isset($request->plazos) && is_array($request->plazos)) {
                foreach ($request->plazos as $descripcion) {
                    Plazo::create([
                        'idAuditorialInterna' => $auditoria->idAuditorialInterna,
                        'descripcion' => $descripcion
                    ]);
                }
            }            

            DB::commit();

            return response()->json([
                'message' => 'Auditoría creada correctamente',
                'auditoria' => $auditoria
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al guardar auditoría', 'details' => $e->getMessage()], 500);
        }
    }

    // Actualizar auditoría existente
    public function update(Request $request, $id)
    {
        $auditoria = AuditoriaInterna::find($id);

        if (!$auditoria) {
            return response()->json(['message' => 'Auditoría no encontrada'], 404);
        }

        try {
            $auditoria->update($request->all());

            return response()->json(['message' => 'Auditoría actualizada correctamente', 'auditoria' => $auditoria]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar auditoría', 'details' => $e->getMessage()], 500);
        }
    }

    // Eliminar auditoría
    public function destroy($id)
    {
        $auditoria = AuditoriaInterna::find($id);

        if (!$auditoria) {
            return response()->json(['message' => 'Auditoría no encontrada'], 404);
        }

        try {
            $auditoria->delete();

            return response()->json(['message' => 'Auditoría eliminada correctamente']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar auditoría', 'details' => $e->getMessage()], 500);
        }
    }
}
