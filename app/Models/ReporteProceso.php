<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ReporteProceso
 * 
 * Representa los reportes generados para un proceso específico dentro del sistema de gestión de calidad.
 * Almacena el nombre del reporte, la fecha de elaboración y el año correspondiente.
 * 
 * Funcionalidades clave:
 * - Se asocia a un proceso (`idProceso`).
 * - Permite guardar reportes de tipo PDF u otros para consulta histórica.
 */
class ReporteProceso extends Model
{
    // Nombre de la tabla
    protected $table = 'ReporteProceso';

    // Clave primaria personalizada
    protected $primaryKey = 'idReporteProceso';

    // Desactiva timestamps automáticos
    public $timestamps = false;

    // Campos asignables en masa
    protected $fillable = [
        'idProceso',
        'nombreReporte',
        'fechaElaboracion',
        'anio'
    ];
}
