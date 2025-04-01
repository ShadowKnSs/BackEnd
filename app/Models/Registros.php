<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

class Registros extends Model
{
    protected $table = 'Registros'; // Nombre de la tabla

    protected $fillable = ['idProceso', 'año']; // Asegúrate de llenar estos campos

    // Opcionalmente, si tienes una columna de timestamps, la puedes desactivar si no es necesaria
    public $timestamps = false; 
}
