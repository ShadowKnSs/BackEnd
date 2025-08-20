<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; // Agregar esta línea

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Agregar HasApiTokens aquí
    
    protected $table = 'usuario';
    protected $primaryKey = 'idUsuario';
    public $timestamps = false;

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

    public function roles()
    {
        return $this->belongsToMany(TipoUsuario::class, 'usuario_tipo', 'idUsuario', 'idTipoUsuario');
    }

    public function tipoPrincipal()
    {
        return $this->belongsTo(TipoUsuario::class, 'idTipoUsuario');
    }

    public function procesosSupervisados()
    {
        return $this->hasMany(SupervisorProceso::class, 'idUsuario');
    }
}