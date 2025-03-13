<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objetivo extends Model
{
    protected $table = 'objetivos';
    protected $primaryKey = 'idObjetivo';

    protected $fillable = [
        'idProyectoMejora',
        'descripcionObj'
    ];

    public $timestamps = false;
}
