<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultadoIndi extends Model
{
    use HasFactory;

    protected $table = 'ResultadoIndi';
    // Asumes que la PK es el mismo idIndicador (1:1 con IndicadoresConsolidados)
    protected $primaryKey = 'idIndicador';
    public $incrementing = false;  // si no quieres que sea autoincremental
    public $timestamps = false;

    protected $fillable = [
        'idIndicador',
        'resultadoAnual',
        'resultadoSemestral1',
        'resultadoSemestral2'
    ];

    public function indicador()
    {
        // belongsTo => la FK local es idIndicador, la PK remota es idIndicador
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
