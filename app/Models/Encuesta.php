<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Encuesta extends Model
{
    use HasFactory;

    protected $table = 'encuesta'; // Asegúrate de que coincida con el nombre real de la tabla
    protected $primaryKey = 'idEncuesta';
    public $timestamps = false; // Si no tienes timestamps

    protected $fillable = [
        'idIndicador',         // FK que relaciona con la tabla AnalisisDatos o Indicadores
        'malo',
        'regular',
        'excelenteBueno',
        'noEncuestas',
        'idformAnalisisDatos',
        'necesidad',
        'interpretacion'
    ];
    public function formAnalisisDatos()
    {
        return $this->belongsTo(formAnalisisDatos::class, 'id_formAnalisisDatos', 'idformAnalisisDatos');
    }
}
