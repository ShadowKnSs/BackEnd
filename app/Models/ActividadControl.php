<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadControl extends Model
{
    use HasFactory;

    protected $table = 'actividadcontrol';
    protected $primaryKey = 'idActividad';
    public $timestamps = true;
    
    protected $fillable = [
        'idProceso',
        'idFormulario',
        'idResponsable',
        'nombreActividad',
        'procedimiento',
        'caracteristicasVerificar',
        'criterioAceptacion',
        'frecuencia',
        'identificacionSalida',
        'registroSalida',
        'tratameinto' // Posible error en el nombre
    ];
}
