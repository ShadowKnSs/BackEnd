<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoIndi extends Model
{
    protected $table = 'ResultadoIndi';

    protected $primaryKey = 'idIndicador';

    public $timestamps = false;

    protected $fillable = [
        'idIndicador',
        'resultadoSemestral1',
        'resultadoSemestral2'
    ];

    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador');
    }
}
