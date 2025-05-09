<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;

class DocumentoController extends Controller
{
    // Listar documentos (opcionalmente por proceso)
    public function index(Request $request)
    {
        $idProceso = $request->query('proceso');
        $documentos = $idProceso
            ? Documento::where('idProceso', $idProceso)->get()
            : Documento::all();

        return response()->json($documentos);
    }

    // Mostrar uno
    public function show($id)
    {
        $documento = Documento::find($id);
        if (!$documento) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        return response()->json($documento);
    }

    // Crear
    public function store(Request $request)
    {
        $data = $request->validate([
            'idProceso' => 'required|integer',
            'nombreDocumento' => 'required|string',
            'codigoDocumento' => 'required|string',
            'tipoDocumento' => 'required|in:interno,externo',
            'fechaRevision' => 'nullable|date',
            'fechaVersion' => 'nullable|date',
            'noRevision' => 'nullable|integer',
            'noCopias' => 'nullable|integer',
            'tiempoRetencion' => 'nullable|integer',
            'lugarAlmacenamiento' => 'nullable|string',
            'medioAlmacenamiento' => 'nullable|in:Físico,Digital,Ambos',
            'disposicion' => 'nullable|string',
            'responsable' => 'nullable|string',
        ]);

        $documento = Documento::create($data);

        return response()->json($documento, 201);
    }

    // Actualizar
    public function update(Request $request, $id)
    {
        $documento = Documento::find($id);
        if (!$documento) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $data = $request->validate([
            'idProceso' => 'sometimes|required|integer',
            'nombreDocumento' => 'sometimes|required|string',
            'codigoDocumento' => 'sometimes|required|string',
            'tipoDocumento' => 'sometimes|required|in:interno,externo',
            'fechaRevision' => 'nullable|date',
            'fechaVersion' => 'nullable|date',
            'noRevision' => 'nullable|integer',
            'noCopias' => 'nullable|integer',
            'tiempoRetencion' => 'nullable|integer',
            'lugarAlmacenamiento' => 'nullable|string',
            'medioAlmacenamiento' => 'nullable|in:Físico,Digital,Ambos',
            'disposicion' => 'nullable|string',
            'responsable' => 'nullable|string',
        ]);

        $documento->update($data);

        return response()->json($documento);
    }

    // Eliminar
    public function destroy($id)
    {
        $documento = Documento::find($id);
        if (!$documento) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $documento->delete();
        return response()->json(['message' => 'Documento eliminado']);
    }
}
