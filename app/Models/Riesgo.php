<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riesgo extends Model
{
    public $timestamps = false;
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'riesgos';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idRiesgo';


    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idGesRies',
        'responsable',
        'fuente',
        'tipoRiesgo',
        'descripcion',
        'consecuencias',
        'valorSeveridad',
        'valorOcurrencia',
        'valorNRP',
        'actividades',
        'accionMejora',
        'fechaImp',
        'fechaEva',
        'reevaluacionSeveridad',
        'reevaluacionOcurrencia',
        'reevaluacionNRP',
        'reevaluacionEfectividad',
        'analisisEfectividad',
    ];

    // Relación con Proceso
    public function gestRies()
    {
        return $this->belongsTo(GestionRiesgos::class, 'idGesRies', 'idGesRies');
    }
}
