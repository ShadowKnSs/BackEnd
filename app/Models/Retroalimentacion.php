<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retroalimentacion extends Model
{
    use HasFactory;

    protected $table = 'retroalimentacion';
    protected $primaryKey = 'idRetro';
    public $timestamps = false;

    protected $fillable = [
        'idIndicador',
        'metodo',   // 'Buzon Virtual','Encuesta','Buzon Fisico'
        'cantidadFelicitacion',
        'cantidadSugerencia',
        'cantidadQueja',
        'total',
        'idProceso'
    ];

    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
