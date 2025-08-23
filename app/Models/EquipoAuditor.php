<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo EquipoAuditor
 * 
 * Representa a los integrantes del equipo auditor asignado a una auditoría interna.
 * Cada miembro puede tener un rol específico (por ejemplo: auditor líder o auditor auxiliar).
 * 
 * Funcionalidades clave:
 * - Pertenece a una `AuditoriaInterna`.
 * - Registra el nombre del auditor, el rol asignado y si es líder.
 */
class EquipoAuditor extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'equipoauditor';

    // Clave primaria personalizada
    protected $primaryKey = 'idEquipoAud';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'idAuditorialInterna',
        'rolAsignado',
        'nombreAuditor',
        'esAuditorLider'
    ];

    /**
     * Relación: este auditor forma parte de una auditoría interna.
     */
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
