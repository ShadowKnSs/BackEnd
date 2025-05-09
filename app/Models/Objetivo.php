<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objetivo extends Model
{
    protected $table = 'Objetivos';
    protected $primaryKey = 'idObjetivo';

    protected $fillable = [
        'idProyectoMejora',
        'descripcionObj'
    ];

    public $timestamps = false;

    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
