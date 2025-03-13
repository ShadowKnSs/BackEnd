<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'Formularios';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idFormulario';

    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idApartado',
        'nombreFormulario'
    ];

    // Si la tabla no tiene timestamps (created_at, updated_at)
    public $timestamps = false;

    // Relación con EstructuraProceso
    public function estructuraProceso()
    {
        return $this->belongsTo(EstructuraProceso::class, 'idApartado', 'idApartado');
    }
}
