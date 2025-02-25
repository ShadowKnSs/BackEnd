<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    // Si el nombre de la tabla es diferente, ajústalo aquí:
    protected $table = 'eventosnotavi';

    // Si la llave primaria es "idEvento"
    protected $primaryKey = 'idEvento';

    // Si la tabla no usa timestamps, establece:
    public $timestamps = false;

    // Define los atributos asignables (fillable)
    protected $fillable = [
        'idUsuario',
        'titulo',
        'descripcion',
        'fechaPublicacion',
        'tipo',         // Puede ser "Aviso", "Noticia" o "Evento"
        'fechaEvento',  // Puede ser null para avisos o noticias
        'rutaImg'
    ];
}
