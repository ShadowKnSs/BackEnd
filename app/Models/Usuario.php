<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
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

    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'idTipoUsuario', 'idTipoUsuario');
    }
}