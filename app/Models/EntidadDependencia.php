<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntidadDependencia extends Model
{
    protected $table = 'entidaddependencia';

    protected $primaryKey = 'idEntidadDependencia';

    protected $fillable = ['ubicacion', 'nombreEntidad' ];

    public function procesos()
    {
        return $this->hasMany(Proceso::class, 'idEntidad', 'idEntidadDependencia');
    }

}
