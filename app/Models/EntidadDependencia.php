<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntidadDependencia extends Model
{
    protected $table = 'entidaddependencia';

    protected $primaryKey = 'idEntidadDependencia';
    public $timestamps = false; 

    protected $fillable = ['ubicacion', 'nombreEntidad', 'tipo', 'icono'];

    public function procesos()
    {
        return $this->hasMany(Proceso::class, 'idEntidad', 'idEntidadDependencia');
    }

}
