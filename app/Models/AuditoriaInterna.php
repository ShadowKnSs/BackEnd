<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaInterna extends Model
{
    protected $table = 'auditoriainterna';
    protected $primaryKey = 'idAuditoriaInterna';
    public $timestamps = false;

    protected $fillable = [
        'idRegistro',
        'fecha',
        'auditorLider'
    ];
}
