<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;
use App\Services\ControlCambiosService;
use Illuminate\Support\Facades\Log;


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
        \Log::debug('📥 Iniciando creación de documento');
        \Log::debug('📦 Datos recibidos', $request->all());
        \Log::debug('📎 Archivos recibidos', $request->allFiles());

        try {
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
                'archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:5120'
            ]);

            \Log::debug('✅ Datos validados correctamente', $data);

            // Generar código automáticamente queda pendiente


            if ($request->hasFile('archivo') && $request->tipoDocumento === 'interno') {
                \Log::debug('📂 Subiendo archivo...');
                $file = $request->file('archivo');
                $path = $file->store('documentos', 'public');
                $data['urlArchivo'] = asset('storage/' . $path);
                \Log::debug('✅ Archivo almacenado en: ' . $data['urlArchivo']);
            } else {
                \Log::debug('⚠️ No se subió archivo o tipoDocumento no es interno');
            }

            $documento = Documento::create($data);
            \Log::debug('✅ Documento creado con ID: ' . $documento->idDocumento);

            ControlCambiosService::registrarCambio(
                $data['idProceso'],
                'Control de documentos',
                'agregó',
                'Documento: ' . $data['nombreDocumento']
            );

            return response()->json($documento, 201);
        } catch (\Throwable $e) {
            \Log::error('❌ Error al crear documento: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error al crear el documento.'], 500);
        }
    }

    // Actualizar
    public function update(Request $request, $id)
    {
        Log::debug("🔧 Iniciando actualización de documento ID: {$id}");

        $documento = Documento::find($id);
        if (!$documento) {
            Log::error("❌ Documento no encontrado con ID: {$id}");
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        try {
            Log::debug("📥 Datos recibidos para actualización", $request->all());

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
                'archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:5120',
            ]);

            $documento->update($data);
            Log::debug("✅ Datos actualizados en el modelo", $data);

            if ($request->hasFile('archivo') && $documento->tipoDocumento === 'interno') {
                $file = $request->file('archivo');
                Log::debug("📎 Archivo recibido para actualizar", ['archivo' => $file->getClientOriginalName()]);

                $path = $file->store('documentos', 'public');
                $documento->urlArchivo = asset('storage/' . $path);
                $documento->save();

                Log::debug("✅ Archivo almacenado en: {$documento->urlArchivo}");
            }

            ControlCambiosService::registrarCambio(
                $documento->idProceso,
                'Control de documentos',
                'editó',
                'Documento: ' . ($data['nombreDocumento'] ?? $documento->nombreDocumento)
            );

            return response()->json($documento);
        } catch (\Throwable $e) {
            Log::error("❌ Error al actualizar documento: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error al actualizar documento'], 500);
        }
    }

    // Eliminar
    public function destroy($id)
    {
        $documento = Documento::find($id);
        if (!$documento) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }
        $idProceso = $documento->idProceso;
        $nombre = $documento->nombreDocumento;
        $documento->delete();

        ControlCambiosService::registrarCambio(
            $idProceso,
            'Control de documentos',
            'eliminó',
            'Documento: ' . $nombre
        );
        return response()->json(['message' => 'Documento eliminado']);
    }
}
