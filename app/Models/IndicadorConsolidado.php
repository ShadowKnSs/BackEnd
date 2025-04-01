<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndicadorConsolidado extends Model
{
    use HasFactory;

    protected $table = 'IndicadoresConsolidados';
    protected $primaryKey = 'idIndicadorConsolidado';

    public $timestamps = false;

    protected $fillable = [
        'nombreIndicador',
        'origenIndicador',
        'periodicidad',
        'meta'
    ];

    // Relación 1:1 con ResultadoIndi (si quieres centralizar resultados)
    public function resultadoIndi()
    {
        return $this->hasOne(ResultadoIndi::class, 'idIndicador', 'idIndicador');
    }

    // Relación 1:1 con Encuesta
    public function encuesta()
    {
        return $this->hasOne(Encuesta::class, 'idIndicador', 'idIndicador');
    }

    // Relación 1:1 con Retroalimentacion
    public function retroalimentacion()
    {
        return $this->hasOne(AnalisisDatos::class, 'idIndicadorConsolidado', 'idIndicadorConsolidado');
    }
}
