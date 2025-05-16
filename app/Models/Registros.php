<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

/**
 * Modelo Registros
 * 
 * Representa un registro general de un proceso para un año y un apartado específico
 * (por ejemplo: Análisis de Datos, Indicadores, Seguimiento, etc.).
 * Este modelo actúa como nodo central para asociar múltiples componentes del sistema.
 * 
 * Funcionalidades clave:
 * - Se vincula directamente a un `Proceso`.
 * - Es base para secciones como análisis de datos, seguimiento, plan de trabajo, etc.
 */
class Registros extends Model
{
    // Nombre de la tabla asociada
    protected $table = 'Registros';

    // Clave primaria personalizada
    protected $primaryKey = 'idRegistro';

    // Atributos que se pueden asignar masivamente
    protected $fillable = ['idProceso', 'año', 'Apartado'];

    // No se usan timestamps automáticos
    public $timestamps = false;

    /**
     * Relación: este registro pertenece a un proceso.
     */
    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }
}
