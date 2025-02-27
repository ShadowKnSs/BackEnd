<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisDatos extends Model
{
    use HasFactory;

    protected $table = 'analisisdatos'; // Ajusta el nombre según tu migración
    protected $primaryKey = 'idIndicador';

    public $timestamps = false;
    protected $fillable = [
        'idIndicadorConsolidado',
        'resultadoSemestral1',
        'resultadoSemestral2',
        'interpretacion',
        'necesidad',
        'meta'
    ];

    // Relación inversa: cada análisis pertenece a un indicador consolidado.
    public function indicadorConsolidado()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicadorConsolidado', 'idIndicadorConsolidado');
    }
}
