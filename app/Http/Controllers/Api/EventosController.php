<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evento;


class EventosController extends Controller
{
     // GET /api/eventos
     public function index()
     {
         $eventos = Evento::all();
         return response()->json(['eventos' => $eventos], 200);
     }
 
     // POST /api/eventos
     public function store(Request $request)
     {
         $validatedData = $request->validate([
             'titulo' => 'required|string|max:255',
             'descripcion' => 'nullable|string',
             'fechaPublicacion' => 'required|date',
             'tipo' => 'required|in:Aviso,Noticia,Evento',
             'fechaEvento' => 'nullable|date',
             'rutaImg' => 'required|string',
             'idUsuario' => 'required|integer',
         ]);
 
         try {
             $evento = Evento::create($validatedData);
 
             Log::info('Evento creado exitosamente', [
                 'id' => $evento->idEvento,
                 'titulo' => $evento->titulo,
                 'usuario' => auth()->user()->name ?? 'Sistema'
             ]);
 
             return response()->json([
                 'message' => 'Evento creado exitosamente',
                 'evento' => $evento
             ], 201);
         } catch (\Exception $e) {
             Log::error('Error al crear evento', [
                 'error' => $e->getMessage(),
                 'datos' => $request->all()
             ]);
 
             return response()->json([
                 'message' => 'Error al crear el evento',
                 'error' => $e->getMessage()
             ], 500);
         }
     }
 
     // GET /api/eventos/{id}
     public function show($id)
     {
         $evento = Evento::findOrFail($id);
         return response()->json(['evento' => $evento], 200);
     }
 
     // PUT/PATCH /api/eventos/{id}
     public function update(Request $request, $id)
     {
         $evento = Evento::findOrFail($id);
 
         $validatedData = $request->validate([
             'titulo' => 'required|string|max:255',
             'descripcion' => 'nullable|string',
             'fechaPublicacion' => 'required|date',
             'tipo' => 'required|in:Aviso,Noticia,Evento',
             'fechaEvento' => 'nullable|date',
             'rutaImg' => 'required|string',
             'idUsuario' => 'required|integer',
         ]);
 
         try {
             $evento->update($validatedData);
             return response()->json(['message' => 'Evento actualizado', 'evento' => $evento], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'message' => 'Error al actualizar el evento',
                 'error' => $e->getMessage()
             ], 500);
         }
     }
 
     // DELETE /api/eventos/{id}
     public function destroy($id)
     {
         $evento = Evento::findOrFail($id);
         try {
             $evento->delete();
             return response()->json(['message' => 'Evento eliminado'], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'message' => 'Error al eliminar el evento',
                 'error' => $e->getMessage()
             ], 500);
         }
     }
}
