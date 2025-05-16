<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Objetivo
 * 
 * Representa un objetivo específico dentro de un proyecto de mejora.
 * Cada objetivo detalla una meta que se busca alcanzar mediante las acciones del proyecto.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `ProyectoMejora`.
 * - Permite registrar múltiples objetivos para un mismo proyecto.
 */
class Objetivo extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'Objetivos';

    // Clave primaria personalizada
    protected $primaryKey = 'idObjetivo';

    // Atributos que pueden asignarse masivamente
    protected $fillable = [
        'idProyectoMejora',
        'descripcionObj'
    ];

    // No utiliza timestamps automáticos
    public $timestamps = false;

    /**
     * Relación: este objetivo pertenece a un proyecto de mejora.
     */
    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
