<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionRiesgos extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'gestionriesgos';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idGesRies';

    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idregistro',
        'elaboro',
        'fechaelaboracion',
    ];

    // Relación con registro
    public function registro()
    {
        return $this->belongsTo(Registro::class, 'idRegistro', 'idregistro');
    }
}
