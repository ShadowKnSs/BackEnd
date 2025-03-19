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
        'entregable'
    ];

    public function planTrabajo()
    {
        return $this->belongsTo(PlanTrabajo::class, 'idPlanTrabajo', 'idPlanTrabajo');
    }
}
