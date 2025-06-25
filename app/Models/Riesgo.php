<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riesgo extends Model
{
    public $timestamps = false;

    protected $table = 'riesgos';

    protected $primaryKey = 'idRiesgo';

    protected $fillable = [
        'idGesRies',
        'idFuente', // <- nuevo campo de relación
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
        'responsable',
    ];

    // Relación con Gestión de Riesgos
    public function gestRies()
    {
        return $this->belongsTo(GestionRiesgos::class, 'idGesRies', 'idGesRies');
    }

    // Relación con Fuente de Plan de Trabajo (fuentept)
    public function fuentePT()
    {
        return $this->belongsTo(FuentePt::class, 'idFuente', 'idFuente');
    }
}
