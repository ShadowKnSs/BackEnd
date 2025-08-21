<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadControl extends Model
{
    use HasFactory;

    protected $table = 'actividadcontrol';
    protected $primaryKey = 'idActividad';
    public $timestamps = false; // si no tienes created_at/updated_at

    protected $fillable = [
        'idProceso',
        'nombreActividad',
        'procedimiento',
        'caracteristicasVerificar',
        'criterioAceptacion',
        'frecuencia',
        'identificacionSalida',
        'registroSalida',
        'tratamiento', 
        'responsable' 
    ];
}
