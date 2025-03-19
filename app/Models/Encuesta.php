<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    use HasFactory;

    protected $table = 'encuesta';
    protected $primaryKey = 'idEncuesta';
    public $timestamps = false;

    protected $fillable = [
        'idIndicador',
        'malo',
        'regular',
        'bueno',
        'excelente',
        'noEncuestas'
    ];

    public function indicador()
    {
        return $this->belongsTo(IndicadorConsolidado::class, 'idIndicador', 'idIndicador');
    }
}
