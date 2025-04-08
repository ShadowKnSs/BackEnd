<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TokenTemporal;
use Illuminate\Support\Str;

class TokenTemporalController extends Controller
{
    public function generar(Request $request)
{
    \Log::info('📥 Llamada al método generar()', ['data' => $request->all()]);

    $request->validate([
        'expirationDateTime' => 'required|date',
    ]);

    $token = strtoupper(Str::random(12));

    \Log::info('🔐 Token generado', ['token' => $token]);

    $nuevoToken = TokenTemporal::create([
        'token' => $token,
        'expiracion' => $request->expirationDateTime,
    ]);

    \Log::info('✅ Token guardado en base de datos', ['idToken' => $nuevoToken->idToken]);

    return response()->json($nuevoToken);
}
}
