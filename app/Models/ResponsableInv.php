<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ResponsableInv
 * 
 * Representa un responsable involucrado en un proyecto de mejora (no necesariamente el principal).
 * Se usa para registrar participantes clave que tienen tareas o responsabilidades dentro del proyecto.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `ProyectoMejora`.
 */
class ResponsableInv extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'ResponsablesInvo';

    // Clave primaria personalizada
    protected $primaryKey = 'idResponsableInvo';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Campos asignables en masa
    protected $fillable = [
        'idProyectoMejora',
        'nombre'
    ];

    /**
     * Relación: este responsable pertenece a un proyecto de mejora.
     */
    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
