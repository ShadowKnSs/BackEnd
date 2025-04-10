<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\NotificacionEnviada;
class NotificacionTestController extends Controller
{
    public function enviarNotificacion($idUsuario)
{
    $notificacion = [
        'id' => uniqid(),
        'title' => 'Auditoría (interna)',
        'description' => 'Proceso: Control Escolar, Entidad: FI, Fecha: 2025-04-15, Hora: 10:00',
        'image' => 'https://via.placeholder.com/60'
    ];

    event(new NotificacionEnviada($idUsuario, $notificacion));

    return response()->json(['mensaje' => 'Notificación enviada']);
}
}
