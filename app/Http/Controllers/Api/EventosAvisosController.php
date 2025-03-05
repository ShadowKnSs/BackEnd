<?php

namespace App\Http\Controllers;

use App\Models\EventoAviso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EventosAvisosController extends Controller
{
    // GET /api/eventos-avisos
    // Se puede filtrar ?tipo=Evento o ?tipo=Aviso
    public function index(Request $request)
    {
        $tipo = $request->query('tipo'); // 'Evento' o 'Aviso'
        if ($tipo) {
            $items = EventoAviso::where('tipo', $tipo)->get();
        } else {
            $items = EventoAviso::all();
        }
        return response()->json($items);
    }

    // POST /api/eventos-avisos
    public function store(Request $request)
    {
        $request->validate([
            'idUsuario' => 'required|integer',
            'tipo' => 'required|in:Evento,Aviso',
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
        ]);

        $rutaImg = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $path = $file->store('public/eventosavisos');
            $rutaImg = Storage::url($path);
        }

        $fechaPublicacion = Carbon::now();

        $item = EventoAviso::create([
            'idUsuario' => $request->idUsuario,
            'tipo' => $request->tipo,
            'rutaImg' => $rutaImg,
            'fechaPublicacion' => $fechaPublicacion
        ]);

        return response()->json($item, 201);
    }

    // GET /api/eventos-avisos/{id}
    public function show($id)
    {
        $item = EventoAviso::findOrFail($id);
        return response()->json($item);
    }

    // PUT /api/eventos-avisos/{id}
    public function update(Request $request, $id)
    {
        $item = EventoAviso::findOrFail($id);

        $request->validate([
            'tipo' => 'required|in:Evento,Aviso',
            'imagen' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
        ]);

        $rutaImg = $item->rutaImg;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $path = $file->store('public/eventosavisos');
            $rutaImg = Storage::url($path);
        }

        $item->update([
            'tipo' => $request->tipo,
            'rutaImg' => $rutaImg,
            // fechaPublicacion no se modifica si no quieres
        ]);

        return response()->json($item);
    }

    // DELETE /api/eventos-avisos/{id}
    public function destroy($id)
    {
        $item = EventoAviso::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
