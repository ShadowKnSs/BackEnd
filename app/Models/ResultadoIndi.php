<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ResultadoIndi
 * 
 * Representa los resultados numéricos asociados a un indicador consolidado.
 * Este modelo almacena el resultado anual y los resultados de ambos semestres.
 * 
 * Funcionalidades clave:
 * - Relación 1:1 con `IndicadorConsolidado` (comparten la clave primaria `idIndicador`).
 * - Se utiliza para evaluar el desempeño y comportamiento del proceso a lo largo del año.
 */
class ResultadoIndi extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'ResultadoIndi';

    // Se utiliza la misma clave primaria que en IndicadorConsolidado
    protected $primaryKey = 'idIndicador';
    public $incrementing = false;  // No es autoincremental
    public $timestamps = false;

    // Campos asignables en masa
    protected $fillable = [
        'idIndicador',
        'resultadoAnual',
        'resultadoSemestral1',
        'resultadoSemestral2'
    ];

    /**
     * Relación con el indicador consolidado al que pertenecen los resultados.
     */
    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
