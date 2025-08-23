<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Encuesta
 * 
 * Representa los resultados de encuestas aplicadas como parte del análisis de satisfacción del cliente.
 * Almacena las respuestas categorizadas (malo, regular, bueno, excelente) y la cantidad total de encuestas aplicadas.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `IndicadorConsolidado`.
 * - Se utiliza como una de las fuentes para calcular indicadores del sistema de gestión de calidad.
 */
class Encuesta extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'encuesta';

    // Clave primaria personalizada
    protected $primaryKey = 'idEncuesta';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Atributos que pueden asignarse masivamente
    protected $fillable = [
        'idIndicador',
        'malo',
        'regular',
        'bueno',
        'excelente',
        'noEncuestas'
    ];

    /**
     * Relación: esta encuesta pertenece a un indicador consolidado.
     */
    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
