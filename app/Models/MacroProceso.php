<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo MacroProceso
 * 
 * Representa un macroproceso dentro del sistema de gestión de calidad.
 * Agrupa múltiples procesos que pertenecen a una misma categoría o función estratégica.
 * 
 * Funcionalidades clave:
 * - Se relaciona con múltiples `Proceso` a través de `idMacroproceso`.
 */
class MacroProceso extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'macroproceso';

    // Clave primaria personalizada
    protected $primaryKey = 'idMacroproceso';

    // Campos que pueden asignarse masivamente
    protected $fillable = ['tipoMacroproceso'];

    /**
     * Relación: un macroproceso puede tener muchos procesos asociados.
     */
    public function procesos()
    {
        return $this->hasMany(Proceso::class, 'idMacroproceso', 'idMacroproceso');
    }
}
