<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadPlan extends Model
{
    protected $table = 'actividadplan';
    protected $primaryKey = 'idActividadPlan';
    public $timestamps = false;

    protected $fillable = [
        'idPlanCorrectivo',
        'responsable',
        'descripcionAct',
        'fechaProgramada',
        'tipo'
    ];

    public function planCorrectivo()
    {
        return $this->belongsTo(PlanCorrectivo::class, 'idPlanCorrectivo', 'idPlanCorrectivo');
    }
}

