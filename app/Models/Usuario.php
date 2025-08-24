<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; // Agregar esta línea

class Usuario extends Authenticatable
{
    //use HasApiTokens, HasFactory, Notifiable; // Agregar HasApiTokens aquí
    
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