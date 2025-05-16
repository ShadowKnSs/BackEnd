<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PlanCorrectivo
 * 
 * Representa un plan de acciones correctivas diseñado para atender una no conformidad detectada.
 * Contiene información sobre el origen del problema, causa raíz, evidencias, responsables y estado.
 * 
 * Funcionalidades clave:
 * - Pertenece a una `ActividadMejora`.
 * - Contiene múltiples actividades correctivas registradas como `ActividadPlan`.
 */
class PlanCorrectivo extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'plancorrectivo';

    // Clave primaria personalizada
    protected $primaryKey = 'idPlanCorrectivo';

    // Timestamps desactivados
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'idActividadMejora',
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

    /**
     * Relación: este plan tiene muchas actividades correctivas asociadas.
     */
    public function actividades()
    {
        return $this->hasMany(ActividadPlan::class, 'idPlanCorrectivo', 'idPlanCorrectivo');
    }

    /**
     * Relación: este plan pertenece a una actividad de mejora.
     */
    public function actividadMejora()
    {
        return $this->belongsTo(ActividadMejora::class, 'idActividadMejora');
    }
}
