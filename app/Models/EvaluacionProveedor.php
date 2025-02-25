<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionProveedor extends Model
{
    use HasFactory;

    protected $table = 'evaluaproveedores';

    protected $fillable = [
        'indicador_id',  // FK a Indicador
        'confiable',     // Valor numérico o porcentaje
        'condicionado',  // Valor numérico o porcentaje
        'no_confiable',  // Valor numérico o porcentaje
        'periodo',       // 'Ene-Jun' o 'Jul-Dic' (si el indicador es semestral), o 'Anual'
    ];

    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }
}
