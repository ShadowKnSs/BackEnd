<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

class Registros extends Model
{
    protected $table = 'Registros'; // Nombre de la tabla
    protected $primaryKey = 'idRegistro';

    protected $fillable = ['idProceso', 'año', 'Apartado'];

    public $timestamps = false; 
}
