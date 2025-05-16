<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo EntidadDependencia
 * 
 * Representa una entidad o dependencia dentro del sistema, como una facultad, escuela o departamento.
 * Se utiliza para organizar los procesos según su origen institucional.
 * 
 * Funcionalidades clave:
 * - Puede tener múltiples procesos asociados.
 * - Almacena información básica como ubicación, tipo, nombre e icono representativo.
 */
class EntidadDependencia extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'entidaddependencia';

    // Clave primaria personalizada
    protected $primaryKey = 'idEntidadDependencia';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = ['ubicacion', 'nombreEntidad', 'tipo', 'icono'];

    /**
     * Relación: esta entidad puede tener muchos procesos asignados.
     */
    public function procesos()
    {
        return $this->hasMany(Proceso::class, 'idEntidad', 'idEntidadDependencia');
    }
}
