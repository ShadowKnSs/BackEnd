<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NoticiasController extends Controller
{
    // GET /api/noticias
    public function index()
    {
        Log::info('[NoticiasController@index] Cargando todas las noticias.');

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
            $noticia = Noticia::create([
                'idUsuario' => $request->idUsuario,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fechaPublicacion' => now(),
                'rutaImg' => null,
            ]);

            if ($request->hasFile('imagen')) {
                if (!Storage::exists('public/img')) {
                    Storage::makeDirectory('public/img');
                }

                $file = $request->file('imagen');
                $orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext  = strtolower($file->getClientOriginalExtension());
                $slug = Str::slug($orig); // quita espacios/acentos
                $fileName = time() . '_' . $slug . '.' . $ext;

                $targetPath = storage_path('app/public/img/' . $fileName);

                $manager = new ImageManager(new GdDriver()); // requiere ext-gd
                $image = $manager->read($file->getRealPath());
                $image->resize(800, 600, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                })->save($targetPath, 80);

                // Guardamos RUTA RELATIVA para evitar duplicar dominio
                $publicPath = Storage::url('img/' . $fileName); // => "/storage/img/..."
                $noticia->update(['rutaImg' => $publicPath]);
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
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg|max:4096'
        ]);

        DB::beginTransaction();
        try {
            $rutaImg = $noticia->rutaImg;

            if ($request->hasFile('imagen')) {
                // Borrar anterior (soporta absoluta o relativa)
                if ($noticia->rutaImg) {
                    if ($path = $this->storagePathFromUrlOrRelative($noticia->rutaImg)) {
                        Storage::delete($path);
                    }
                }

                if (!Storage::exists('public/img')) {
                    Storage::makeDirectory('public/img');
                }

                $file = $request->file('imagen');
                $orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext  = strtolower($file->getClientOriginalExtension());
                $slug = Str::slug($orig);
                $fileName = time() . '_' . $slug . '.' . $ext;

                $filePath = storage_path('app/public/img/' . $fileName);

                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($file->getRealPath());
                $image->resize(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save($filePath, 80);

                $rutaImg = Storage::url('img/' . $fileName); // relativa
            }

            $noticia->update([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'rutaImg' => $rutaImg
            ]);

            DB::commit();
            return response()->json($noticia);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PUT /api/noticias failed', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'No se pudo actualizar la noticia',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/noticias/{id}
    public function destroy($id)
    {
        Log::info('[NoticiasController@destroy] Eliminando noticia con ID: ' . $id);

        $noticia = Noticia::findOrFail($id);

        if ($noticia->rutaImg) {
            if ($path = $this->storagePathFromUrlOrRelative($noticia->rutaImg)) {
                Storage::delete($path);
            }
        }

        $noticia->delete();

        Log::info('[NoticiasController@destroy] Noticia con ID: ' . $id . ' eliminada correctamente.');

        return response()->json(['message' => 'Noticia eliminada correctamente']);
    }

    /**
     * Convierte una URL absoluta o ruta relativa de /storage/... a path interno de Storage "public/...".
     * Ejemplos:
     *  - https://dominio.com/storage/img/a.jpg -> public/img/a.jpg
     *  - /storage/img/a.jpg                    -> public/img/a.jpg
     *  - public/img/a.jpg                      -> public/img/a.jpg
     */
    private function storagePathFromUrlOrRelative(string $ruta): ?string
    {
        // Si ya viene en formato "public/..."
        if (str_starts_with($ruta, 'public/')) {
            return $ruta;
        }

        // Extraer path de una URL absoluta
        $path = parse_url($ruta, PHP_URL_PATH) ?: $ruta; // si no es URL, deja tal cual

        // Normalizar cuando arranca con "/storage/..."
        if (str_starts_with($path, '/storage/')) {
            return 'public/' . ltrim(substr($path, strlen('/storage/')), '/');
        }

        // Si ya es relativo a storage (raro), intenta mapear
        if (str_starts_with($path, 'storage/')) {
            return 'public/' . ltrim(substr($path, strlen('storage/')), '/');
        }

        // No reconocible
        return null;
    }
}
