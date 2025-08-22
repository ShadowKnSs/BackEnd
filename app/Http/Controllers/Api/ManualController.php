<?php
// app/Http/Controllers/ManualController.php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualController extends Controller
{
    public function download($rol)
    {
        try {
            // Validar que el rol existe en nuestro mapeo
            $rolesPermitidos = ['administrador', 'coordinador', 'líder', 'supervisor', 'auditor'];
            
            if (!in_array(strtolower($rol), $rolesPermitidos)) {
                return response()->json(['error' => 'Rol no válido'], 404);
            }
            
            // Mapeo de archivos por rol
            $archivosPorRol = [
                'administrador' => 'manuales/administrador.pdf',
                'coordinador' => 'manuales/coordinador.pdf',
                'líder' => 'manuales/lider.pdf',
                'supervisor' => 'manuales/supervisor.pdf',
                'auditor' => 'manuales/auditor.pdf',
            ];
            
            $rutaArchivo = $archivosPorRol[strtolower($rol)];
            
            // Verificar si el archivo existe
            if (!Storage::disk('public')->exists($rutaArchivo)) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }
            
            // Descargar el archivo
            return Storage::disk('public')->download($rutaArchivo);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al descargar el archivo'], 500);
        }
    }
}