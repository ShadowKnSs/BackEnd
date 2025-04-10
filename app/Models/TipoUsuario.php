<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = 'tipousuario';
    protected $primaryKey = 'idTipoUsuario';

    protected $fillable = [
        'nombreRol',
        'descripcion'
    ];

    public function usuario(){
        return $this->hasMany(Usuario::class, 'idTipoUsuario', 'idTipoUsuario');
    }
}