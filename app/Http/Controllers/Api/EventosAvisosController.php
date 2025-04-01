<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventoAviso;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventosAvisosController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');
        $query = EventoAviso::query();
        
        if ($tipo) {
            $query->where('tipo', $tipo);
        }
        
        return response()->json($query->get());
    }

    public function store(Request $request)
{
    $request->validate([
        'idUsuario' => 'required|integer',
        'tipo' => 'required|in:Evento,Aviso',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

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

    $item = EventoAviso::create([
        'idUsuario' => $request->idUsuario,
        'tipo' => $request->tipo,
        'rutaImg' => $rutaImg,
        'fechaPublicacion' => Carbon::now()
    ]);

    return response()->json($item, 201);
}


    public function show($id)
    {
        return response()->json(EventoAviso::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $item = EventoAviso::findOrFail($id);

        $request->validate([
            'tipo' => 'required|in:Evento,Aviso',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $rutaImg = $item->rutaImg;
        
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior
            if ($item->rutaImg) {
                $oldPath = str_replace('/storage', 'public', $item->rutaImg);
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

        $item->update([
            'tipo' => $request->tipo,
            'rutaImg' => $rutaImg,
        ]);

        return response()->json($item);
    }

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