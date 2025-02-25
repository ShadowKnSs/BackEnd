<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GestionRiesgo extends Model
{
    use HasFactory;

    protected $table = 'gestionriesgo';

    protected $primaryKey = 'idRiesgo';

    public $timestamps = false;

    protected $fillable = [
        'indicador_id',    // FK a Indicador
        'idArchivo',
        'idResponsable',
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
        'reevaluacionOcurrencia',
        'reevaluacionNRP',
        'reevaluacionEfectividad',
        'analisisEfectividad'
    ];

    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }
}
