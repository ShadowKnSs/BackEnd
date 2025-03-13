<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoMejora extends Model
{
    protected $table = 'proyectomejora';
    protected $primaryKey = 'idProyectoMejora';
    protected $fillable = [
        'idActividadMejora', 'responsable', 'fecha', 'noMejora', 'descripcionMejora', 'objetivo',
        'areaImpacto', 'personalBeneficiado', 'situacionActual', 'indicadorExito', 'aprobacionNombre', 'aprobacionPuesto'
    ];
}

