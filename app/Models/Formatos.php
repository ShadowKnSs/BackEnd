<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Formatos
 * 
 * Representa un formato documental cargado o creado por un usuario dentro del sistema.
 * Cada formato incluye su nombre, la ruta del archivo y el usuario que lo registró.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `Usuario`.
 * - Permite centralizar la gestión de formatos oficiales en el sistema.
 */
class Formatos extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'Formatos';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    // Clave primaria
    protected $primaryKey = 'idFormato';
    public $incrementing = true;

    // Campos asignables en masa
    protected $fillable = [
        'idUsuario',
        'nombreFormato',
        'ruta',
    ];

    /**
     * Relación: este formato fue registrado por un usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
}
