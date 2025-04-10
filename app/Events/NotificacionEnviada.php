<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class NotificacionEnviada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use InteractsWithSockets, SerializesModels;

    public $usuarioId;
    public $notificacion;

    public function __construct($usuarioId, $notificacion)
    {
        $this->usuarioId = $usuarioId;
        $this->notificacion = $notificacion;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notificaciones.usuario.' . $this->usuarioId);
    }

    public function broadcastAs()
    {
        return 'nueva-notificacion';
    }

    /**
     * Create a new event instance.
     */
    
}
