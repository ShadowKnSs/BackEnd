<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ReporteAuditoria
 * 
 * Representa un reporte generado a partir de una auditoría interna.
 * Contiene información relevante como fecha de generación, hallazgos detectados,
 * oportunidades de mejora y ubicación del archivo generado.
 * 
 * Funcionalidades clave:
 * - Se relaciona con una auditoría interna específica (`idAuditorialInterna`).
 * - Permite registrar y acceder a los reportes emitidos para seguimiento.
 */
class ReporteAuditoria extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'reportesauditoria';

    // Clave primaria
    protected $primaryKey = 'idReporte';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Atributos que pueden asignarse en masa
    protected $fillable = [
        'idAuditorialInterna',
        'fechaGeneracion',
        'hallazgo',
        'oportunidadesMejora',
        'cantidadAuditoria',
        'ruta',
    ];

    /**
     * Relación: este reporte pertenece a una auditoría interna.
     */
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
