<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo FuentePt
 * 
 * Representa una fuente documental o elemento de entrada dentro de un plan de trabajo.
 * Cada fuente incluye información sobre fechas, responsable, estado, y entregables definidos.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `PlanTrabajo`.
 * - Permite detallar los recursos y documentos utilizados en la elaboración del plan.
 */
class FuentePt extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'fuentept';

    // Clave primaria personalizada
    protected $primaryKey = 'idFuente';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Atributos asignables en masa
    protected $fillable = [
        'idPlanTrabajo',
        'noActividad',
        'responsable',
        'fechaInicio',
        'fechaTermino',
        'estado',
        'nombreFuente',
        'elementoEntrada',
        'descripcion',
        'entregable',
    ];

    /**
     * Relación: esta fuente pertenece a un plan de trabajo.
     */
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
