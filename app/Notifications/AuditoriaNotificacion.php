<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditoriaNotificacion extends Notification
{
    public $cronograma;
    public $usersname;
    public $emails;

    // Recibir datos en el constructor
    public function __construct($cronograma, $usersname, $emails)
    {
        $this->cronograma = $cronograma;
        $this->usersname = $usersname;
        $this->emails = $emails;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
                    ->subject('Notificación de Auditoría')
                    ->greeting('Hola,')
                    ->line('Se ha creado una nueva auditoría con la siguiente información:')
                    ->line('Tipo de Auditoría: ' . $this->cronograma['tipoAuditoria'])
                    ->line('Fecha Programada: ' . $this->cronograma['fechaProgramada'])
                    ->line('Hora Programada: ' . $this->cronograma['horaProgramada'])
                    ->line('Proceso: ' . $this->cronograma['nombreProceso'])
                    ->line('Entidad: ' . $this->cronograma['nombreEntidad'])
                    ->line('Auditores asignados:');

        foreach ($this->usersname as $user) {
            $mail->line("- " . $user);
        }

        $mail->line('Gracias por su atención.');

        return $mail;
    }

     // Método para guardar en la base de datos
     public function toDatabase(object $notifiable)
     {
         return [
             'type' => 'Auditoria Notificación',
             'data' => [
                 'tipoAuditoria' => $this->cronograma['tipoAuditoria'],
                 'fechaProgramada' => $this->cronograma['fechaProgramada'],
                 'horaProgramada' => $this->cronograma['horaProgramada'],
                 'nombreProceso' => $this->cronograma['nombreProceso'],
                 'nombreEntidad' => $this->cronograma['nombreEntidad'],
                 'auditores' => $this->usersname,  // Aquí guardas los auditores asignados
             ],
         ];
     }

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
