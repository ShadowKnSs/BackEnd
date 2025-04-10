<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;

class NotificacionController extends Controller
{
    //
    public function getNotificaciones($idUsuario)
    {
        $usuario = Usuario::find($idUsuario);

        if (!$usuario) {
            Log::warning("Usuario con ID $idUsuario no encontrado.");
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        Log::info("Usuario encontrado: " . $usuario->nombre);

        $notificaciones = $usuario->unreadNotifications()->orderBy('created_at', 'desc')->get();


        Log::info("Total de notificaciones encontradas: " . $notificaciones->count());

        $notificacionesFormateadas = $notificaciones->map(function ($notificacion) {
            $data = $notificacion->data;

            // Accedemos al segundo nivel 'data'
            $info = $data['data'] ?? [];

            Log::info("Datos internos de la notificación {$notificacion->id}:", $info);

            return [
                'id' => $notificacion->id,
                'accion' => $info['accion'] ?? null,
                'tipoAuditoria' => $info['tipoAuditoria'] ?? null,
                'fechaProgramada' => $info['fechaProgramada'] ?? null,
                'horaProgramada' => $info['horaProgramada'] ?? null,
                'nombreProceso' => $info['nombreProceso'] ?? null,
                'nombreEntidad' => $info['nombreEntidad'] ?? null,
                'usuarios' => $info['usuarios'] ?? null,
                'leida' => $notificacion->read_at !== null,
                'fechaCreacion' => $notificacion->created_at->toDateTimeString(),
            ];
        });
        Log::info("Notificaciones formateadas:", $notificacionesFormateadas->toArray());

        return response()->json([
            'usuario' => $usuario->nombre,
            'notificaciones' => $notificacionesFormateadas
        ]);
    }


    public function marcarComoLeidas($idUsuario, $idNotificacion)
    {
        // Verificar si el usuario existe
        $usuario = Usuario::find($idUsuario);

        // Log para verificar si encontramos al usuario
        \Log::info("Buscando usuario con ID: $idUsuario");

        if (!$usuario) {
            // Si no se encuentra el usuario, logueamos y retornamos error
            \Log::error("Usuario no encontrado: $idUsuario");
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Buscar la notificación específica del usuario
        $notificacion = $usuario->unreadNotifications->where('id', $idNotificacion)->first();

        // Log para verificar si encontramos la notificación
        \Log::info("Buscando notificación con ID: $idNotificacion para el usuario: $idUsuario");

        if (!$notificacion) {
            // Si no se encuentra la notificación, logueamos y retornamos error
            \Log::error("Notificación no encontrada para el usuario $idUsuario con ID: $idNotificacion");
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        // Marcar la notificación como leída
        \Log::info("Marcando la notificación ID $idNotificacion como leída para el usuario $idUsuario");

        $notificacion->markAsRead(); // Laravel ya tiene este método

        // Log después de marcar la notificación como leída
        \Log::info("Notificación ID $idNotificacion marcada como leída exitosamente.");

        return response()->json(['message' => 'Notificación marcada como leída']);
    }
    public function contarNotificacionesNoLeidas($idUsuario)
    {
        $usuario = Usuario::find($idUsuario);

        if (!$usuario) {
            Log::warning("Usuario con ID $idUsuario no encontrado.");
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $cantidad = $usuario->unreadNotifications()->count();

        Log::info("Usuario {$usuario->nombre} tiene $cantidad notificaciones no leídas.");

        return response()->json([
            'usuario' => $usuario->nombre,
            'notificacionesNoLeidas' => $cantidad
        ]);
    }


}
