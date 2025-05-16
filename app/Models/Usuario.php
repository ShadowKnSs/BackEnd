<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Usuario
 * 
 * Representa a un usuario del sistema SGC. Puede tener múltiples roles y estar asociado
 * a procesos que supervisa. Este modelo extiende de `Authenticatable` para permitir autenticación.
 * 
 * Funcionalidades clave:
 * - Acceso a roles múltiples mediante `usuario_tipo`.
 * - Rol principal asociado mediante `idTipoUsuario`.
 * - Supervisión de procesos específicos mediante `supervisor_proceso`.
 */
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tabla correspondiente en la base de datos
    protected $table = 'usuario';

    // Clave primaria personalizada
    protected $primaryKey = 'idUsuario';

    // No se manejan timestamps automáticos
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'idTipoUsuario',
        'nombre',
        'apellidoPat',
        'apellidoMat',
        'telefono',
        'correo',
        'gradoAcademico',
        'activo',
        'fechaRegistro',
        'RPE',
        'pass'
    ];

    /**
     * Relación muchos a muchos con roles (usuario puede tener varios).
     */
    public function roles()
    {
        return $this->belongsToMany(TipoUsuario::class, 'usuario_tipo', 'idUsuario', 'idTipoUsuario');
    }

    /**
     * Rol principal del usuario (asociación directa).
     */
    public function tipoPrincipal()
    {
        return $this->belongsTo(TipoUsuario::class, 'idTipoUsuario');
    }

    /**
     * Procesos que el usuario supervisa.
     */
    public function procesosSupervisados()
    {
        return $this->hasMany(SupervisorProceso::class, 'idUsuario');
    }
}
