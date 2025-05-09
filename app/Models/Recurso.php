<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    protected $table = 'recursos';
    protected $primaryKey = 'idRecursos';
    public $timestamps = false;

    protected $fillable = [
        'idProyectoMejora',
        'tiempoEstimado',
        'recursosMatHum',
        'costo',
    ];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
