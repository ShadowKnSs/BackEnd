<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorProceso extends Model
{
    protected $table = 'supervisor_proceso';
    protected $primaryKey = 'idSupervisorProceso';

    protected $fillable = ['idUsuario', 'idProceso'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso');
    }
}
