<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuscadorSem extends Model
{
    protected $table = 'ReporteSemestral';

    protected $primaryKey = 'idReporteSemestral';

    protected $fillable = [
        'idReporteSemestral',
        'anio',
        'periodo',
        'fortalezas',
        'debilidades',
        'conclusion',
        'fechaGeneracion'
    ];
}
