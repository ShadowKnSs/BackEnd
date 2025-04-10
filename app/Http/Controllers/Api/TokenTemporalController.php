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
        date_default_timezone_set('America/Mexico_City');

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

        $ahora = Carbon::now();
        $expiracion = Carbon::parse($registro->expiracion);

        Log::info('🕐 Validando expiración del token', [
            'ahora' => $ahora->toDateTimeString(),
            'expiracion' => $expiracion->toDateTimeString(),
        ]);

        if ($ahora->greaterThan($expiracion)) {
            return response()->json([
                'message' => 'El token ha expirado',
            ], 401);
        }

        return response()->json([
            'message' => 'Token válido',
        ], 200);
    }
    public function index()
    {
        return TokenTemporal::all();
    }

    public function destroy($id)
    {
        $token = TokenTemporal::findOrFail($id);
        $token->delete();
        return response()->json(['message' => 'Token eliminado']);
    }
}
