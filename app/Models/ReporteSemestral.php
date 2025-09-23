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
    protected $table = 'ReporteSemestral';
    protected $primaryKey = 'idReporteSemestral';
    public $incrementing = true; // <<<<< importante
    protected $keyType = 'int';  // <<<<< importante
    public $timestamps = false;

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

