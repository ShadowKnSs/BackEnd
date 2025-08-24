<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Retroalimentacion
 * 
 * Representa los datos recopilados por medios de retroalimentación del cliente (como buzón físico, virtual o encuestas).
 * Se usa principalmente para el análisis de satisfacción en el sistema de gestión de calidad.
 * 
 * Funcionalidades clave:
 * - Registra cantidades de felicitaciones, sugerencias y quejas.
 * - Se vincula a un indicador consolidado y a un proceso.
 */
class Retroalimentacion extends Model
{
    use HasFactory;

    // Tabla correspondiente
    protected $table = 'retroalimentacion';

    // Clave primaria
    protected $primaryKey = 'idRetro';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Atributos asignables en masa
    protected $fillable = [
        'idIndicador',
        'metodo',   // 'Buzon Virtual','Encuesta','Buzon Fisico'
        'cantidadFelicitacion',
        'cantidadSugerencia',
        'cantidadQueja',
        'total',
        'idProceso'
    ];

    /**
     * Relación: esta retroalimentación pertenece a un indicador consolidado.
     */
    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
