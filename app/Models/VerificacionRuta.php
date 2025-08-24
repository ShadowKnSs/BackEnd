<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo VerificacionRuta
 * 
 * Representa los criterios de verificación asociados a una auditoría interna.
 * Cada registro describe un criterio, requisito asociado, observaciones, evidencia encontrada
 * y el tipo de hallazgo (no conformidad, oportunidad de mejora, etc.).
 * 
 * Relación:
 * - Pertenece a una AuditoriaInterna (idAuditorialInterna).
 */
class VerificacionRuta extends Model
{
    use HasFactory;

    // Tabla relacionada en la base de datos
    protected $table = 'verificacionruta';

    // Llave primaria personalizada
    protected $primaryKey = 'idCriterio';

    // No se manejan timestamps (created_at, updated_at)
    public $timestamps = false;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'idAuditorialInterna',
        'criterio',
        'reqAsociado',
        'observaciones',
        'evidencia',
        'tipoHallazgo'
    ];

    /**
     * Relación con la auditoría interna correspondiente.
     */
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
