<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacroProceso extends Model
{
    protected $table = 'macroproceso';
    protected $primaryKey = 'idMacroproceso';

    protected $fillable = ['tipoMacroproceso'];

    // Un macroproceso puede tener muchos procesos
    public function procesos()
    {
        return $this->hasMany(Proceso::class, 'idMacroproceso', 'idMacroproceso');
    }
}
