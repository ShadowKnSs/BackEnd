<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GraficaController extends Controller
{
    public function guardar(Request $request)
{
    $request->validate([
        'imagenBase64' => 'required|string',
        'nombre' => 'nullable|string',
    ]);

    Log::info("📥 Recibido en guardar():", [
        'nombre' => $request->input('nombre'),
        'base64_length' => strlen($request->input('imagenBase64', '')),
        'base64_inicio' => substr($request->input('imagenBase64', ''), 0, 60)
    ]);

    try {
        $base64 = $request->input('imagenBase64');
        $nombreArchivo = $request->input('nombre') ?? 'grafica_' . Str::random(10);

        // Verificar si es un base64 válido de imagen
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $imagen = substr($base64, strpos($base64, ',') + 1);
            $extension = strtolower($type[1]);

            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
                Log::warning("⚠️ Extensión no soportada", ['extensión' => $extension]);
                return response()->json(['error' => 'Extensión no soportada'], 422);
            }
        } else {
            // Si no tiene el formato esperado, verificar si es base64 válido
            Log::warning("⚠️ Formato base64 sin prefijo, verificando si es base64 válido");
            
            // Verificar si es una cadena base64 válida
            if (base64_decode($base64, true) === false) {
                Log::warning("⚠️ Cadena base64 inválida", ['base64' => substr($base64, 0, 100)]);
                return response()->json(['error' => 'Base64 inválida'], 422);
            }
            
            $imagen = $base64;
            $extension = 'png'; // Asumir PNG por defecto
        }

        $decodedImage = base64_decode($imagen, true);
        if ($decodedImage === false) {
            Log::warning("⚠️ Fallo en la decodificación base64");
            return response()->json(['error' => 'Base64 inválida'], 422);
        }

        $filename = "$nombreArchivo.$extension";
        $ruta = "graficas/$filename";

        // Guardar archivo
        Storage::disk('public')->put($ruta, $decodedImage);
        $url = asset("storage/graficas/$filename");

        Log::info("✅ Imagen guardada correctamente", [
            'archivo' => $filename,
            'url' => $url,
            'tamaño' => strlen($decodedImage)
        ]);

        return response()->json([
            'message' => 'Imagen guardada correctamente',
            'url' => $url,
            'filename' => $filename
        ], 201);
    } catch (\Exception $e) {
        Log::error("❌ Error al guardar la imagen", [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Error al guardar la imagen'], 500);
    }
}
    
}
