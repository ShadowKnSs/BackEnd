<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventoAviso;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventosAvisosController extends Controller
{
    // GET /api/eventos-avisos?tipo=Evento|Aviso
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');

        $query = EventoAviso::select('idEventosAvisos', 'fechaPublicacion', 'rutaImg', 'tipo'); // puedes quitar 'tipo' si no lo usas

        if ($tipo) {
            $query->where('tipo', $tipo); // filtro dinámico
        }

        $result = $query->get();

        return response()
            ->json($result, 200)
            ->header('Cache-Control', 'public, max-age=300');
    }

    // POST /api/eventos-avisos
    public function store(Request $request)
    {
        $request->validate([
            'idUsuario' => ['required', 'integer', 'exists:usuario,idUsuario'],
            'tipo' => ['required', 'in:Evento,Aviso'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
        ]);

        $rutaImg = null;

        // Si hay imagen, se redimensiona antes de guardar
        if ($request->hasFile('imagen')) {
            // Asegura directorio
            if (!Storage::exists('public/img')) {
                Storage::makeDirectory('public/img');
            }
            $file = $request->file('imagen');
            $orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($file->getClientOriginalExtension());
            $slug = Str::slug($orig);
            $fileName = time() . '_' . $slug . '.' . $ext;
            $filePath = storage_path('app/public/img/' . $fileName);

            //  Redimensionar y guardar la imagen
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $image->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($filePath, 80); // 80% calidad

            $rutaImg = Storage::url('img/' . $fileName); // => "/storage/img/..."
        }

        // Se crea el registro

        $item = EventoAviso::create([
            'idUsuario' => $request->idUsuario,
            'tipo' => $request->tipo,
            'rutaImg' => $rutaImg,
            'fechaPublicacion' => Carbon::now()
        ]);

        return response()->json($item, 201);
    }


    // GET /api/eventos-avisos/{id}
    public function show($id)
    {
        return response()->json(EventoAviso::findOrFail($id));
    }

    //  PUT /api/eventos-avisos/{id}

    public function update(Request $request, $id)
    {
        $item = EventoAviso::findOrFail($id);

        $request->validate([
            'tipo' => 'required|in:Evento,Aviso',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $rutaImg = $item->rutaImg;

        //  Si hay nueva imagen, se reemplaza
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($item->rutaImg) {
                if ($path = $this->storagePathFromUrlOrRelative($item->rutaImg)) {
                    Storage::delete($path);
                }
            }

            if (!Storage::exists('public/img')) {
                Storage::makeDirectory('public/img');
            }

            // Guardar nueva imagen redimensionada
            $file = $request->file('imagen');
            $orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($file->getClientOriginalExtension());
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

        $item->update([
            'tipo' => $request->tipo,
            'rutaImg' => $rutaImg,
        ]);

        return response()->json($item);
    }


    //  DELETE /api/eventos-avisos/{id}

    public function destroy($id)
    {
        $item = EventoAviso::findOrFail($id);

        if ($item->rutaImg) {
            if ($path = $this->storagePathFromUrlOrRelative($item->rutaImg)) {
                Storage::delete($path);
            }
        }

        $item->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }

    /**
     * Convierte URL absoluta o ruta relativa de /storage/... a path interno "public/..."
     */
    private function storagePathFromUrlOrRelative(string $ruta): ?string
    {
        if (str_starts_with($ruta, 'public/')) {
            return $ruta;
        }
        $path = parse_url($ruta, PHP_URL_PATH) ?: $ruta;
        if (str_starts_with($path, '/storage/')) {
            return 'public/' . ltrim(substr($path, strlen('/storage/')), '/');
        }
        if (str_starts_with($path, 'storage/')) {
            return 'public/' . ltrim(substr($path, strlen('storage/')), '/');
        }
        return null;
    }
}