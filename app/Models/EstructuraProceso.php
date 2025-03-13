<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstructuraProceso extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'EstructuraProceso';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idApartado';

    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'nombreApartado'
    ];

    // Si la tabla no tiene timestamps (created_at, updated_at)
    public $timestamps = false;
}
