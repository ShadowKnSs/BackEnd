<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo EvaluaProveedores
 * 
 * Representa los datos de evaluación de proveedores, clasificados en confiables, condicionados y no confiables.
 * Se utiliza como fuente de información para un indicador consolidado relacionado al desempeño de proveedores.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `IndicadorConsolidado`.
 * - Permite registrar resultados por semestre y metas específicas por categoría.
 */
class EvaluaProveedores extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'evaluaProveedores';

    // Clave primaria personalizada
    protected $primaryKey = 'idEvaProveedores';

    // Desactiva timestamps automáticos
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'idIndicador',
        'confiable',
        'noConfiable',
        'condicionado',
        'metaCondicionado',
        'metaNoConfiable',
        'resultadoConfiableSem1',
        'resultadoConfiableSem2',
        'resultadoCondicionadoSem1',
        'resultadoCondicionadoSem2',
        'resultadoNoConfiableSem1',
        'resultadoNoConfiableSem2'
    ];

    /**
     * Relación: esta evaluación pertenece a un indicador consolidado.
     */
    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
