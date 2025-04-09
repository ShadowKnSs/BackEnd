<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditorias'; 

    protected $primaryKey = 'idAuditoria';

    public $timestamps = false;

    protected $fillable = [
        'idProceso',
        'fechaProgramada',
        'horaProgramada',
        'tipoAuditoria',
        'estado',
        'descripcion',
        'nombreProceso',
        'nombreEntidad',
    ];
}
