<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluaProveedores extends Model
{
    use HasFactory;

    protected $table = 'evaluaProveedores';
    protected $primaryKey = 'idEvaProveedores';
    public $timestamps = false;

    protected $fillable = [
        'idIndicador',
        'confiable',
        'noConfiable',
        'condicionado',
        'metaCondicionado',
        'metaNoConfiable',
        'resultadoConfiableSem1',
        'resultadoConfiableSem2',
        'resultadoCondicionadoSem1',
        'resultadoCondicionadoSem2',
        'resultadoNoConfiableSem1',
        'resultadoNoConfiableSem2'

    ];

    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
