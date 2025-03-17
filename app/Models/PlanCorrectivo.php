<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanCorrectivo extends Model
{
    protected $table = 'plancorrectivo';
    protected $primaryKey = 'idPlanCorrectivo';
    public $timestamps = false; // O true, si usas timestamps

    protected $fillable = [
        'idRegistro',
        'fechaInicio',
        'origenConformidad',
        'equipoMejora',
        'requisito',
        'incumplimiento',
        'evidencia',
        'revisionAnalisis',
        'causaRaiz',
        'estadoSimilares',
        'estadoConformidad',
        'coordinadorPlan',
        'entidad',
        'codigo'
    ];

    public function actividades()
    {
        return $this->hasMany(ActividadPlan::class, 'idPlanCorrectivo', 'idPlanCorrectivo');
    }
}

