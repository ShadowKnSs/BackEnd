<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo MapaProceso
 * 
 * Representa el mapa de proceso de una entidad o área, incluyendo entradas, salidas,
 * documentos utilizados, materiales, puestos involucrados y un diagrama visual del flujo.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `Proceso`.
 * - Permite documentar gráficamente cómo opera un proceso dentro del sistema de gestión de calidad.
 */
class MapaProceso extends Model {
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'mapaproceso';

    // Clave primaria personalizada
    protected $primaryKey = 'idMapaProceso';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'idProceso', 
        'documentos', 
        'fuente', 
        'material', 
        'requisitos', 
        'salidas', 
        'receptores', 
        'puestosInvolucrados',
        'diagramaFlujo'
    ];

    /**
     * Relación: este mapa pertenece a un proceso.
     */
    public function proceso() {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }
}
