<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Retroalimentacion extends Model
{
    use HasFactory;

    protected $table = 'retroalimentacion';
    protected $primaryKey = 'idRetro';
    public $timestamps = false;

    protected $fillable = [
        'idIndicador',      // Se asume que es la FK al indicador consolidado
        'metodo',           // Debe ser uno de los valores del ENUM ('Buzón Virtual', 'Encuesta', 'Buzón Físico')
        'cantidadFelicitacion',
        'cantidadSugerencia',
        'cantidadQueja'
    ];

    // Relación: cada registro de retroalimentación pertenece a un indicador.
    public function indicador()
    {
        return $this->belongsTo(AnalisisDatos::class, 'idIndicador', 'idAnalisisDatos');
    }
}
