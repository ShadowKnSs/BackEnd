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

        Log::info('[NoticiasController@index] Se cargaron '.count($noticias).' noticias.');

        return response()->json([$noticias], 200);
    }

    // POST /api/noticias
    public function store(Request $request)
    {
        Log::info('[NoticiasController@store] Iniciando creación de noticia.', [
            'request_data' => $request->all()
        ]);

        //Valida campos
        $request->validate([
            'idUsuario' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
        ]);
      
        //dd($request->all(), $request->file('imagen'));
        //Manejo de la imagen
        $rutaImg = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $path = $file->store('public/img'); 
// Por defecto: /storage/img/xxxxx.png

            $relativeUrl = Storage::url($path); // "/storage/img/xxxxx.png"

// Generamos la URL absoluta con la URL de la app
            $absoluteUrl = config('app.url') . $relativeUrl;
// => "http://localhost:8000/storage/img/xxxxx.png"

            $rutaImg = $relativeUrl;

            Log::info('[NoticiasController@store] Imagen guardada en: '.$rutaImg);
        }

        //Manejo de fecha
        $fechaPublicacion = Carbon::now();

        $noticia = Noticia::create([
            'idUsuario' => $request->idUsuario,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fechaPublicacion' => $fechaPublicacion,
            'rutaImg' => $rutaImg
        ]);

        Log::info('[NoticiasController@store] Noticia creada con ID: '.$noticia->idNoticias);

        return response()->json($noticia, 201);
    }

    // GET /api/noticias/{id}
    public function show($id)
    {
        Log::info('[NoticiasController@show] Cargando noticia con ID: '.$id);

        $noticia = Noticia::findOrFail($id);

        Log::info('[NoticiasController@show] Noticia encontrada: '.$noticia->titulo);

        return response()->json($noticia);
    }

    // PUT /api/noticias/{id}
    public function update(Request $request, $id)
    {
        Log::info('[NoticiasController@update] Iniciando actualización de noticia con ID: '.$id, [
            'request_data' => $request->all()
        ]);

        $noticia = Noticia::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg'
        ]);

        $rutaImg = $noticia->rutaImg;

        if ($request->hasFile('imagen')) {
            Log::info('[NoticiasController@update] Se subió una nueva imagen.');

            $file = $request->file('imagen');
            $path = $file->store('public/img'); 
// Por defecto: /storage/img/xxxxx.png

        $relativeUrl = Storage::url($path); // "/storage/img/xxxxx.png"

// Generamos la URL absoluta con la URL de la app
        $absoluteUrl = config('app.url') . $relativeUrl;
// => "http://localhost:8000/storage/img/xxxxx.png"

        $rutaImg = $relativeUrl;

            Log::info('[NoticiasController@update] Nueva imagen guardada en: '.$rutaImg);
        }

        $noticia->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'rutaImg' => $rutaImg
        ]);

        Log::info('[NoticiasController@update] Noticia actualizada con ID: '.$id);

        return response()->json($noticia);
    }

    // DELETE /api/noticias/{id}
    public function destroy($id)
    {
        Log::info('[NoticiasController@destroy] Eliminando noticia con ID: '.$id);

        $noticia = Noticia::findOrFail($id);
        $noticia->delete();

        Log::info('[NoticiasController@destroy] Noticia con ID: '.$id.' eliminada correctamente.');

        return response()->json(['message' => 'Noticia eliminada correctamente']);
    }
}
