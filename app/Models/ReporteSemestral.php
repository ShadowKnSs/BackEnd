<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ReporteSemestral
 * 
 * Representa los reportes semestrales generados en el sistema de gestión de calidad.
 * Cada reporte contiene un análisis general del periodo, incluyendo fortalezas, debilidades y conclusiones.
 * 
 * Funcionalidades clave:
 * - Almacena la información cualitativa y la ubicación del archivo generado.
 * - No utiliza timestamps automáticos.
 */
class ReporteSemestral extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'ReporteSemestral';

    // Clave primaria personalizada
    protected $primaryKey = 'idReporteSemestral';

    // No se manejan created_at ni updated_at
    public $timestamps = false;

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'anio',
        'periodo',
        'fortalezas',
        'debilidades',
        'conclusion',
        'fechaGeneracion',
        'ubicacion'
    ];
}
