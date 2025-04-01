<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteProceso extends Model
{
    // Especifica el nombre de la tabla en la base de datos
    protected $table = 'ReporteProceso';

    // Especifica la clave primaria
    protected $primaryKey = 'idReporteProceso';

    // Si la tabla no tiene los campos created_at y updated_at
    public $timestamps = false;

    // Los campos que se pueden asignar en forma masiva
    protected $fillable = [
        'idProceso',
        'nombreReporte',
        'fechaElaboracion',
        'anio'
    ];
}
