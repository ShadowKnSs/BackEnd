<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventoAviso;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventosAvisosController extends Controller
{
        // GET /api/eventos-avisos?tipo=Evento|Aviso
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');
        
        $query = EventoAviso::select('idEventosAvisos', 'fechaPublicacion','rutaImg', 'tipo'); // puedes quitar 'tipo' si no lo usas
        
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
    'idUsuario' => ['required','integer','exists:usuario,idUsuario'],
    'tipo'      => ['required','in:Evento,Aviso'],
    'imagen'    => ['nullable','image','mimes:jpeg,png,jpg,gif','max:2048'],
]);

        $rutaImg = null;

        // Si hay imagen, se redimensiona antes de guardar
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = storage_path('app/public/img/' . $fileName);

            //  Redimensionar y guardar la imagen
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $image->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($filePath, 80); // 80% calidad

            $rutaImg = config('app.url') . Storage::url('img/' . $fileName);
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
                $oldPath = str_replace(config('app.url') . '/storage', 'public', $item->rutaImg);
                Storage::delete($oldPath);
            }

            // Guardar nueva imagen redimensionada
            $file = $request->file('imagen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = storage_path('app/public/img/' . $fileName);

            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $image->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($filePath, 80);

            $rutaImg = config('app.url') . Storage::url('img/' . $fileName);
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
            $oldPath = str_replace('/storage', 'public', $item->rutaImg);
            Storage::delete($oldPath);
        }

        $item->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }
}