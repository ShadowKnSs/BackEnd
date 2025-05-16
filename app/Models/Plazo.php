<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Plazo
 * 
 * Representa un plazo establecido dentro del contexto de una auditoría interna.
 * Este modelo se utiliza para registrar compromisos, tiempos de respuesta o fechas límite relacionadas.
 * 
 * Funcionalidades clave:
 * - Se vincula directamente a una auditoría interna mediante `idAuditorialInterna`.
 * - Almacena una descripción libre del plazo.
 */
class Plazo extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'plazos';

    // Clave primaria personalizada
    protected $primaryKey = 'idPlazo';

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'idAuditorialInterna',
        'descripcion'
    ];

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    /**
     * Relación: este plazo pertenece a una auditoría interna.
     */
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
