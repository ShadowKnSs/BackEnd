<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Formatos;
use Illuminate\Support\Facades\Storage;

class FormatosController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombreFormato' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB máximo
        ]);

        // Guardar archivo en storage
        $ruta = $request->file('archivo')->store('formatos', 'public');

        // Guardar en la base de datos
        $formato = Formatos::create([
            'idUsuario' => $request->idUsuario,
            'nombreFormato' => $request->nombreFormato,
            'ruta' => $ruta,
        ]);

        return response()->json([
            'message' => 'Formato guardado correctamente',
            'formato' => $formato
        ], 201);
    }

    public function index()
    {
        $formatos = Formatos::all();

        return response()->json($formatos);
    }
}