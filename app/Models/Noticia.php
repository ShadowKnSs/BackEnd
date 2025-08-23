<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Modelo Noticia
 * 
 * Representa una noticia o publicación informativa dentro del sistema.
 * Almacena título, descripción, fecha de publicación, imagen asociada y el autor (usuario).
 * 
 * Funcionalidades clave:
 * - Relación con `Usuario` mediante `idUsuario`.
 * - Usa Carbon para dar formato legible a `fechaPublicacion`.
 * - No utiliza timestamps automáticos.
 */
class Noticia extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'Noticias';

    // Clave primaria personalizada
    protected $primaryKey = 'idNoticias';

    // No se usan timestamps automáticos
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'idUsuario',
        'titulo',
        'descripcion',
        'fechaPublicacion',
        'rutaImg'
    ];

    // Fecha tratada como objeto Carbon
    protected $dates = ['fechaPublicacion'];

    /**
     * Accesor para formatear la fecha de publicación como 'dd-mm-YYYY HH:mm'
     */
    public function getFechaPublicacionAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i');
    }
}
