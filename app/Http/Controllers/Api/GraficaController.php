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
            'base64_inicio' => substr($request->input('imagenBase64', ''), 0, 60)
        ]);
        
    
        try {
            $base64 = $request->input('imagenBase64');
            $nombreArchivo = $request->input('nombre') ?? 'grafica_' . Str::random(10);
    
            Log::info("🖼️ Intentando guardar imagen base64", ['nombre' => $nombreArchivo]);
    
            // Validar formato base64
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $imagen = substr($base64, strpos($base64, ',') + 1);
                $extension = strtolower($type[1]);
    
                if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
                    Log::warning("⚠️ Extensión no soportada", ['extensión' => $extension]);
                    return response()->json(['error' => 'Extensión no soportada'], 422);
                }
    
                $imagen = base64_decode($imagen);
                if ($imagen === false) {
                    Log::warning("⚠️ Fallo en la decodificación base64");
                    return response()->json(['error' => 'Base64 inválida'], 422);
                }
            } else {
                Log::warning("⚠️ Formato base64 inválido recibido");
                return response()->json(['error' => 'Formato base64 inválido'], 422);
            }
    
            $filename = "$nombreArchivo.$extension";
            $ruta = "graficas/$filename";
    
            // Guardar archivo
            Storage::disk('public')->put($ruta, $imagen);
            $url = asset("storage/graficas/$filename");
    
            Log::info("✅ Imagen guardada correctamente", [
                'archivo' => $filename,
                'url' => $url
            ]);
    
            return response()->json([
                'message' => 'Imagen guardada correctamente',
                'url' => $url,
                'filename' => $filename
            ], 201);
        } catch (\Exception $e) {
            Log::error("❌ Error al guardar la imagen", [
                'exception' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al guardar la imagen'], 500);
        }
    }
    
}
