<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndMapaProceso extends Model
{
    use HasFactory;

    protected $table = 'indmapaproceso';

    protected $primaryKey = 'idIndicadorMP';

    public $timestamps = false;

    protected $fillable = [
        'mapa_proceso_id',  // FK a la tabla de MapaProceso
        'indicador_id',     // FK a Indicador
        'idResponsable',
        'descripcion',
        'formula',
        'periodoMed'
    ];

    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }
}
