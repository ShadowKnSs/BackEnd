<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo IndicadorConsolidado
 * 
 * Representa un indicador registrado dentro del sistema de gestión de calidad, consolidado desde diversas fuentes:
 * encuestas, retroalimentación, evaluación de proveedores o actividades de control.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `Registro` y se asocia a un `Proceso`.
 * - Se vincula con entidades específicas según su origen: `Encuesta`, `Retroalimentacion`, `EvaluaProveedores`.
 * - Puede tener un resultado consolidado mediante `ResultadoIndi`.
 */
class IndicadorConsolidado extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'IndicadoresConsolidados';

    // Clave primaria personalizada
    protected $primaryKey = 'idIndicador';

    // No utiliza timestamps automáticos
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'idRegistro',
        'idProceso',
        'nombreIndicador',
        'origenIndicador',
        'periodicidad',
        'meta'
    ];

    /**
     * Resultado consolidado asociado (1:1).
     */
    public function resultadoIndi()
    {
        return $this->hasOne(ResultadoIndi::class, 'idIndicador', 'idIndicador');
    }

    /**
     * Fuente: encuesta de satisfacción (1:1).
     */
    public function encuesta()
    {
        return $this->hasOne(Encuesta::class, 'idIndicador', 'idIndicador');
    }

    /**
     * Fuente: retroalimentación del cliente (1:1).
     */
    public function retroalimentacion()
    {
        return $this->hasOne(Retroalimentacion::class, 'idIndicador', 'idIndicador');
    }

    /**
     * Fuente: evaluación de proveedores (1:1).
     */
    public function evaluaProveedores()
    {
        return $this->hasOne(EvaluaProveedores::class, 'idIndicador', 'idIndicador');
    }

    /**
     * Relación: este indicador pertenece a un registro.
     */
    public function registro()
    {
        return $this->belongsTo(Registros::class, 'idRegistro', 'idRegistro');
    }

    public function indicadorMapaProceso()
    {
        return $this->hasOne(IndMapaProceso::class, 'idIndicador', 'idIndicador');
    }
}
