<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo IndicadoresExito
 * 
 * Representa los indicadores clave que se utilizarán para medir el éxito de un proyecto de mejora.
 * Cada indicador incluye un nombre y una meta asociada, y se vincula directamente al proyecto.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `ProyectoMejora`.
 * - Permite evaluar el impacto y la efectividad del proyecto una vez implementado.
 */
class IndicadoresExito extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'IndicadoresExito';

    // Clave primaria personalizada
    protected $primaryKey = 'idIndicadorExito';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Atributos que pueden asignarse masivamente
    protected $fillable = [
        'idProyectoMejora',
        'nombreInd',
        'meta',
    ];

    /**
     * Relación: este indicador pertenece a un proyecto de mejora.
     */
    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
