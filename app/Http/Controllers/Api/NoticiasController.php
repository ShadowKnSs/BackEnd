<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class NoticiasController extends Controller
{
    // GET /api/noticias
    public function index()
    {
        Log::info('[NoticiasController@index] Cargando todas las noticias.');

        // Solo obtenemos los campos necesarios
        $noticias = Noticia::select('idNoticias', 'titulo', 'descripcion', 'fechaPublicacion', 'rutaImg')->get();

        Log::info('[NoticiasController@index] Se cargaron ' . count($noticias) . ' noticias.');

        return response()
            ->json($noticias, 200)
            ->header('Cache-Control', 'public, max-age=300');
    }

    // POST /api/noticias

    public function store(Request $request)
    {
        $request->validate([
            'idUsuario' => 'required|integer',
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|file|mimes:jpg,jpeg,png|max:4096'
        ]);

        DB::beginTransaction();
        try {
            // Crear base
            $noticia = Noticia::create([
                'idUsuario' => $request->idUsuario,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fechaPublicacion' => now(),
                'rutaImg' => null,
            ]);

            if ($request->hasFile('imagen')) {
                // Asegura directorio
                if (!Storage::exists('public/img')) {
                    Storage::makeDirectory('public/img');
                }

                $file = $request->file('imagen');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $targetPath = storage_path('app/public/img/' . $fileName);

                // Procesar imagen (Intervention v3 + GD)
                $manager = new ImageManager(new GdDriver()); // requiere ext-gd
                $image = $manager->read($file->getRealPath());
                $image->resize(800, 600, function ($c) {
                    $c->aspectRatio();
                    $c->upsize(); })
                    ->save($targetPath, 80);

                $publicUrl = config('app.url') . Storage::url('img/' . $fileName);
                $noticia->update(['rutaImg' => $publicUrl]);
            }

            DB::commit();
            return response()->json($noticia, 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('POST /api/noticias failed', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'No se pudo crear la noticia',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
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
            $filePath = storage_path('app/public/img/' . $fileName);

            // Redimensionar con Intervention v3
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $image->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($filePath, 80);

            $rutaImg = config('app.url') . Storage::url('img/' . $fileName);
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
