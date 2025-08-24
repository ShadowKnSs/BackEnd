<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; 

/**
 * Modelo EventoAviso
 * 
 * Representa un evento o aviso institucional publicado por un usuario.
 * Cada registro contiene el tipo de contenido (evento o aviso), la fecha de publicación
 * y la ruta de la imagen correspondiente.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `Usuario` (relación implícita).
 * - Formatea la fecha de publicación al formato `d-m-Y H:i` usando Carbon.
 */
class EventoAviso extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'EventosAvisos';

    // Clave primaria personalizada
    protected $primaryKey = 'idEventosAvisos';

    // No utiliza timestamps automáticos
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'idUsuario',
        'fechaPublicacion',
        'tipo',
        'rutaImg'
    ];

    /**
     * Accesor: formatea la fecha de publicación como 'd-m-Y H:i'
     */
    public function getFechaPublicacionAttribute($value)
    {
        if (!$value) return null;
        return Carbon::parse($value)->format('d-m-Y H:i');
    }
}
