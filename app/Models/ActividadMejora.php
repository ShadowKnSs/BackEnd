<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadMejora extends Model
{
    protected $table = 'ActividadMejora'; // nombre de la tabla en minúsculas (según convención)
    protected $primaryKey = 'idActividadMejora';
    public $timestamps = false;

    protected $fillable = [
        'idRegistro'
    ];

    // Se asume que cada actividad de mejora se relaciona (1:1 o 1:N) con plantrabajo.
    // En este ejemplo definimos una relación one-to-one.
    public function planTrabajo()
    {
        return $this->hasOne(PlanTrabajo::class, 'idActividadMejora', 'idActividadMejora');
    }
}
