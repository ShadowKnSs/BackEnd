<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TokenTemporal;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class TokenTemporalController extends Controller
{
    public function generar(Request $request)
    {
        Log::info('📥 Llamada al método generar()', ['data' => $request->all()]);

        $request->validate([
            'expirationDateTime' => 'required|date',
        ]);

        $token = strtoupper(Str::random(12));

        Log::info('🔐 Token generado', ['token' => $token]);

        $nuevoToken = TokenTemporal::create([
            'token' => $token,
            'expiracion' => $request->expirationDateTime,
        ]);

        Log::info('✅ Token guardado en base de datos', ['idToken' => $nuevoToken->idToken]);

        return response()->json($nuevoToken);
    }

    public function validar(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');

        $registro = TokenTemporal::where('token', $token)->first();

        if (!$registro) {
            return response()->json([
                'message' => 'Token no válido',
            ], 401);
        }

        if (Carbon::now()->greaterThan($registro->expiracion)) {
            return response()->json([
                'message' => 'El token ha expirado',
            ], 401);
        }

        return response()->json([
            'message' => 'Token válido',
        ], 200);
    }
}
