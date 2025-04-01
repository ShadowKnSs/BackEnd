<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormAnalisisDatos extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'formAnalisisDatos';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'id_formAnalisisDatos';

    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idRegistro',
        'periodoEva',
    ];

    // Relación con la tabla registros
    public function registro()
    {
        return $this->belongsTo(Registro::class, 'idRegistro', 'idRegistro');
    }
}