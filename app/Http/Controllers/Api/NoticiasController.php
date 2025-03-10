<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importamos Log

class NoticiasController extends Controller
{
    // GET /api/noticias
    public function index()
    {
        Log::info('[NoticiasController@index] Cargando todas las noticias.');

        $noticias = Noticia::all();

        Log::info('[NoticiasController@index] Se cargaron ' . count($noticias) . ' noticias.');

        return response()->json($noticias, 200);
    }

    // POST /api/noticias
    public function store(Request $request)
    {
        // 1. Validar
        $request->validate([
            'idUsuario' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
        ]);

        // 2. Crear el registro SIN la imagen
        $fechaPublicacion = now();
        $noticia = Noticia::create([
            'idUsuario' => $request->idUsuario,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fechaPublicacion' => $fechaPublicacion,
            'rutaImg' => null // por ahora vacío
        ]);

        // 3. Subir la imagen con nombre usando $noticia->id
        $rutaImg = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'img/' . $fileName;
    
            // Guardamos el archivo en /storage/app/public/img
            Storage::disk('public')->putFileAs('img', $file, $fileName);
    
            // Ruta absoluta (opcional)
            $rutaImg = config('app.url') . Storage::url($filePath);
        }
    
        // 4. Actualizar la noticia con la ruta final
        $noticia->rutaImg = $rutaImg;
        $noticia->save();

       

        return response()->json($noticia, 201);
    }


    // GET /api/noticias/{id}
    public function show($id)
    {
        Log::info('[NoticiasController@show] Cargando noticia con ID: ' . $id);

        $noticia = Noticia::findOrFail($id);

        Log::info('[NoticiasController@show] Noticia encontrada: ' . $noticia->titulo);

        return response()->json($noticia);
    }

    // PUT /api/noticias/{id}
    public function update(Request $request, $id)
    {
        $noticia = Noticia::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
        ]);

        $rutaImg = $noticia->rutaImg;

        // Si hay nueva imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior
            if ($noticia->rutaImg) {
                $oldPath = str_replace('/storage', 'public', $noticia->rutaImg);
                Storage::delete($oldPath);
            }
            
            // Guardar nueva imagen
            $file = $request->file('imagen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'img/' . $fileName;
            Storage::disk('public')->putFileAs('img', $file, $fileName);

            // Ruta absoluta (opcional)
            $rutaImg = config('app.url') . Storage::url($filePath);
        }

        // Actualizar
        $noticia->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'rutaImg' => $rutaImg
        ]);

        return response()->json($noticia);
    }


    // DELETE /api/noticias/{id}
    public function destroy($id)
    {
        Log::info('[NoticiasController@destroy] Eliminando noticia con ID: ' . $id);

        $noticia = Noticia::findOrFail($id);

        if ($noticia->rutaImg) {
            $oldPath = str_replace('/storage', 'public', $noticia->rutaImg);
            Storage::delete($oldPath);
        }

        $noticia->delete();

        Log::info('[NoticiasController@destroy] Noticia con ID: ' . $id . ' eliminada correctamente.');

        return response()->json(['message' => 'Noticia eliminada correctamente']);
    }
}
