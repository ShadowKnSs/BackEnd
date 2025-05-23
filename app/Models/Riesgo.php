<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Riesgo
 * 
 * Representa un riesgo identificado dentro de un proceso. Contiene toda la información relacionada con
 * la evaluación inicial, acciones de mejora, fechas de implementación y reevaluaciones.
 * 
 * Funcionalidades clave:
 * - Se asocia a una gestión de riesgos (`GestionRiesgos`) mediante `idGesRies`.
 * - Incluye valores de severidad, ocurrencia y NRP (Número de Riesgo Prioritario).
 * - Permite registrar acciones de mejora y su efectividad.
 */
class Riesgo extends Model
{
    // No se utilizan timestamps automáticos (created_at, updated_at)
    public $timestamps = false;

    // Nombre de la tabla correspondiente
    protected $table = 'riesgos';

    // Clave primaria personalizada
    protected $primaryKey = 'idRiesgo';

    // Atributos asignables en masa
    protected $fillable = [
        'idGesRies',
        'responsable',
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
    ];

    /**
     * Relación: este riesgo pertenece a una gestión de riesgos específica.
     */
    public function gestRies()
    {
        return $this->belongsTo(GestionRiesgos::class, 'idGesRies', 'idGesRies');
    }
}
