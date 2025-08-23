<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo TokenTemporal
 * 
 * Representa los tokens generados para usuarios temporales, usados comúnmente para accesos
 * limitados en el tiempo, como invitados o validaciones externas.
 * 
 * Funcionalidades clave:
 * - Guarda el token y su fecha de expiración.
 * - No incluye timestamps automáticos.
 */
class TokenTemporal extends Model
{
    // Tabla asociada
    protected $table = 'usuarios_temporales';

    // Clave primaria
    protected $primaryKey = 'idToken';

    // Atributos que pueden ser asignados masivamente
    protected $fillable = [
        'token',
        'expiracion',
    ];

    // No utiliza created_at ni updated_at
    public $timestamps = false;
}
