<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuditoriaNotificacion extends Notification
{
    public $cronograma;
    public $usersname;
    public $emails;

    public $accion;

    public function __construct($cronograma, $usersname, $emails, $accion)
    {
        $this->cronograma = $cronograma;
        $this->usersname = $usersname;
        $this->emails = $emails;
        $this->accion = $accion; // 'creada', 'actualizada', 'eliminada'
    }


    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Notificación de Auditoría')
            ->greeting('Hola,')
            ->line("Se ha {$this->accion} una auditoría con la siguiente información:");

        if ($this->cronograma['tipoAuditoria'] === 'interna' && count($this->usersname) === 2) {
            $mail->line('Tipo de Auditoría: Interna')
                ->line('Fecha Programada: ' . $this->cronograma['fechaProgramada'])
                ->line('Hora Programada: ' . $this->cronograma['horaProgramada'])
                ->line('Proceso: ' . $this->cronograma['nombreProceso'])
                ->line('Entidad: ' . $this->cronograma['nombreEntidad'])
                ->line('Líder del Proceso: ' . $this->usersname[1])
                ->line('Auditor Líder: ' . $this->usersname[0]);
        } elseif ($this->cronograma['tipoAuditoria'] === 'externa' && count($this->usersname) === 1) {
            $mail->line('Tipo de Auditoría: Externa')
                ->line('Fecha Programada: ' . $this->cronograma['fechaProgramada'])
                ->line('Hora Programada: ' . $this->cronograma['horaProgramada'])
                ->line('Proceso: ' . $this->cronograma['nombreProceso'])
                ->line('Entidad: ' . $this->cronograma['nombreEntidad'])
                ->line('Líder del Proceso: ' . $this->usersname[0]);
        } else {
            $mail->line('Información de usuarios no disponible o incorrecta.');
        }

        $mail->line('Gracias por su atención. Saludos,');

        return $mail;
    }



    // Método para guardar en la base de datos
    public function toDatabase(object $notifiable)
    {
        return [
            'type' => 'Auditoría Notificación',
            'data' => [
                'accion' => $this->accion,
                'tipoAuditoria' => $this->cronograma['tipoAuditoria'],
                'fechaProgramada' => $this->cronograma['fechaProgramada'],
                'horaProgramada' => $this->cronograma['horaProgramada'],
                'nombreProceso' => $this->cronograma['nombreProceso'],
                'nombreEntidad' => $this->cronograma['nombreEntidad'],
                'usuarios' => $this->usersname,
            ],
        ];
    }

    /* public function toBroadcast($notifiable)
     {
         return new BroadcastMessage([
             'tipoAuditoria' => $this->cronograma['tipoAuditoria'],
             'fechaProgramada' => $this->cronograma['fechaProgramada'],
             'horaProgramada' => $this->cronograma['horaProgramada'],
             'nombreProceso' => $this->cronograma['nombreProceso'],
             'nombreEntidad' => $this->cronograma['nombreEntidad'],
             'auditores' => $this->cronograma['auditores'],
             'read_at' => null,
             'created_at' => now()->toDateTimeString(),
         ]);
     }*/

    use Queueable;
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    // Notificación en base de datos
}
