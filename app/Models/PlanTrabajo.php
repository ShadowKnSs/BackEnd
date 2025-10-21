<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PlanTrabajo
 * 
 * Representa el plan de trabajo diseñado como parte de una actividad de mejora.
 * Incluye información clave como objetivo, responsable, fechas de elaboración y revisión, entregables y estado del plan.
 * 
 * Funcionalidades clave:
 * - Pertenece a una `ActividadMejora`.
 * - Se relaciona con múltiples fuentes documentales (`FuentePt`).
 */
class PlanTrabajo extends Model
{
    // Nombre de la tabla asociada
    protected $table = 'plantrabajo';

    // Clave primaria personalizada
    protected $primaryKey = 'idPlanTrabajo';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Campos asignables en masa
    protected $fillable = [
        'idActividadMejora',
        'responsable',
        'fechaElaboracion',
        'objetivo',
        'fechaRevision',
        'revisadoPor',
        'elaboradoPor',
        'estado',
        'entregable'
    ];

    /**
     * Relación: este plan de trabajo pertenece a una actividad de mejora.
     */
    public function actividadMejora()
    {
        return $this->belongsTo(ActividadMejora::class, 'idActividadMejora', 'idActividadMejora');
    }

    /**
     * Relación: este plan puede tener múltiples fuentes documentales asociadas.
     */
    public function fuentes()
    {
        return $this->hasMany(FuentePt::class, 'idPlanTrabajo', 'idPlanTrabajo');
    }
}
