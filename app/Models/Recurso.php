<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{

    protected $table = 'recursos';
    protected $primaryKey = 'idRecursos';
    // Campos asignables masivamente
    protected $fillable = [
        'idProyectoMejora',
        'descripcionRec',
        'recursosMatHum',
        'costo',
    ];

    // Deshabilitar timestamps si la tabla no tiene created_at y updated_at
    public $timestamps = false;

}