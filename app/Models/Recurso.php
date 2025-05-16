<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Recurso
 * 
 * Representa los recursos requeridos para la implementación de un proyecto de mejora.
 * Estos pueden incluir recursos materiales, humanos, tiempos estimados y costos asociados.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `ProyectoMejora`.
 * - Permite registrar y consultar los recursos estimados para ejecutar el plan de mejora.
 */
class Recurso extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'recursos';

    // Clave primaria personalizada
    protected $primaryKey = 'idRecursos';

    // No utiliza timestamps automáticos
    public $timestamps = false;

    // Atributos asignables en masa
    protected $fillable = [
        'idProyectoMejora',
        'tiempoEstimado',
        'recursosMatHum',
        'costo',
    ];

    /**
     * Relación: este recurso pertenece a un proyecto de mejora.
     */
    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
