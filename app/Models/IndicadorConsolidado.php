<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndicadorConsolidado extends Model
{
    use HasFactory;

    protected $table = 'IndicadoresConsolidados';
    protected $primaryKey = 'idIndicador';

    public $timestamps = false;
    protected $fillable = [
        'idRegistro',
        'nombreIndicador',
        'origenIndicador',
        'periodicidad'
    ];

    // Relación: Un indicador puede tener un análisis de datos (o varios, según tu lógica)
    public function analisisDatos()
    {
        return $this->hasOne(AnalisisDatos::class, 'idIndicadorConsolidado', 'idIndicador');
    }
}
