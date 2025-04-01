<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteSemestral extends Model
{
    protected $table = 'ReporteSemestral';
    protected $primaryKey = 'idReporteSemestral';
    public $timestamps = false;

    protected $fillable = [
        'anio',
        'periodo',
        'fortalezas',
        'debilidades',
        'conclusion',
        'fechaGeneracion',
        'ubicacion'
    ];
}
