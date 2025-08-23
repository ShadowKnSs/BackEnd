<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo PuntosMejora
 * 
 * Representa los puntos de mejora identificados durante una auditoría interna.
 * Cada punto incluye el requisito ISO relacionado, una descripción detallada y evidencia documentada.
 * 
 * Funcionalidades clave:
 * - Se vincula directamente a una `AuditoriaInterna`.
 * - Permite registrar y consultar oportunidades de mejora detectadas.
 */
class PuntosMejora extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'puntosmejora';

    // Clave primaria personalizada
    protected $primaryKey = 'idPunto';

    // No utiliza timestamps automáticos
    public $timestamps = false;

    // Atributos que pueden asignarse en masa
    protected $fillable = [
        'idAuditorialInterna',
        'reqISO',
        'descripcion',
        'evidencia'
    ];

    /**
     * Relación: este punto de mejora pertenece a una auditoría interna.
     */
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
