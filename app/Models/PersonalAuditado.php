<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo PersonalAuditado
 * 
 * Representa a los miembros del personal que fueron auditados durante una auditoría interna.
 * Almacena su nombre y cargo, y se relaciona directamente con una auditoría.
 * 
 * Funcionalidades clave:
 * - Se vincula a una `AuditoriaInterna` mediante `idAuditorialInterna`.
 * - Permite registrar múltiples personas auditadas por auditoría.
 */
class PersonalAuditado extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'personalauditado';

    // Clave primaria personalizada
    protected $primaryKey = 'idPersonalAud';

    // No se usan timestamps automáticos
    public $timestamps = false;

    // Campos asignables en masa
    protected $fillable = ['idAuditorialInterna', 'nombre', 'cargo'];

    /**
     * Relación: este miembro auditado pertenece a una auditoría interna.
     */
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
