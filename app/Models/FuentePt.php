<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuentePt extends Model
{
    protected $table = 'fuentept';
    protected $primaryKey = 'idFuente';
    public $timestamps = false;

    protected $fillable = [
        'idPlanTrabajo',
        'numero',
        'responsable',
        'fechaInicio',
        'fechaTermino',
        'estado',
        'nombreFuente',
        'elementoEntrada',
        'descripcion',
        'entregable',
    ];

    // Relación con Plan de Trabajo
    public function planTrabajo()
    {
        return $this->belongsTo(PlanTrabajo::class, 'idPlanTrabajo', 'idPlanTrabajo');
    }

    // Relación con Riesgos
    public function riesgos()
    {
        return $this->hasMany(Riesgo::class, 'idFuente', 'idFuente');
    }
}
