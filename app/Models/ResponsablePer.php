<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ResponsablePer
 * 
 * Representa un responsable asignado a un proyecto de mejora.
 * Almacena el nombre del responsable y su relación con el proyecto correspondiente.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `ProyectoMejora`.
 * - Puede haber múltiples responsables por proyecto.
 */
class ResponsablePer extends Model
{
    // Nombre de la tabla asociada
    protected $table = 'responsableper';

    // Clave primaria personalizada
    protected $primaryKey = 'idResponsable';

    // Atributos asignables en masa
    protected $fillable = [
        'idProyectoMejora',
        'nombreRes'
    ];

    // No se utilizan timestamps automáticos
    public $timestamps = false;
}
