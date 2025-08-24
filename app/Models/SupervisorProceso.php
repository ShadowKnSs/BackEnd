<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo SupervisorProceso
 * 
 * Representa la relación entre un usuario con rol de supervisor y un proceso específico.
 * Esta tabla funciona como una tabla intermedia personalizada (no many-to-many directa).
 * 
 * Funcionalidades clave:
 * - Asocia un `Usuario` con un `Proceso` como supervisor.
 * - Facilita consultas por proceso o por usuario.
 */
class SupervisorProceso extends Model
{
    // Tabla correspondiente
    protected $table = 'supervisor_proceso';

    // Clave primaria personalizada
    protected $primaryKey = 'idSupervisorProceso';

    // Atributos que pueden asignarse masivamente
    protected $fillable = ['idUsuario', 'idProceso'];

    /**
     * Usuario que actúa como supervisor.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    /**
     * Proceso que está siendo supervisado.
     */
    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso');
    }
}
