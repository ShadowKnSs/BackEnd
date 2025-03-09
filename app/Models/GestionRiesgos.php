<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionRiesgos extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'gestionriesgo';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idRiesgo';

    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idFormulario',
        'responsable',
        'elaboro',
        'fechaElaboracion',
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
        'reevaluacionOcurencia',
        'reevaluacionNRP',
        'reevaluacionEfectividad',
        'analisisEfectividad'
    ];

    // Relación con Proceso
    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }
}
