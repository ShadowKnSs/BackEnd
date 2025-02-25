<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadControl extends Model
{
    use HasFactory;

    protected $table = 'actividadcontrol';

    protected $primaryKey = 'idActividad';

    public $timestamps = false;

    protected $fillable = [
        'indicador_id', // FK a Indicador
        'idArchivo',
        'idResponsable',
        'nombreActividad',
        'procedimiento',
        'caracteristicasVerificar',
        'criterioAceptacion',
        'frecuencia',
        'identificacionSalida',
        'registroSalida',
        'tratamiento'
    ];

    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }
}
