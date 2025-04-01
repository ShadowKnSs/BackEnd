<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsablePer extends Model
{
    protected $table = 'responsableper';
    protected $primaryKey = 'idResponsable';

    protected $fillable = [
        'idProyectoMejora',
        'nombreRes'
    ];

    public $timestamps = false;
}
