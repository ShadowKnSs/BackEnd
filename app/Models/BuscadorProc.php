<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BuscadorProc extends Model
{
    protected $table = 'ReporteProceso';
    protected $primaryKey = 'idReporteProceso';
    public $timestamps = false;

    protected $fillable = [
        'idProceso',
        'nombreReporte',
        'fechaElaboracion'
    ];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }
}