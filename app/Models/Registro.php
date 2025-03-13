<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'registros';

    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idRegistro';

    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idProceso',
        'idFormulario',
        'año'
    ];

    // Si la tabla no tiene timestamps (created_at, updated_at)
    public $timestamps = false;

    // Relación con la tabla Procesos
    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }

    // Relación con la tabla Formularios (asumiendo que el modelo se llama Formulario)
    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'idFormulario', 'idFormulario');
    }
}
