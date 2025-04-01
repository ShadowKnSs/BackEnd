<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BuscadorAudi extends Model
{
    protected $table = 'reportesauditoria';
    protected $primaryKey = 'idReporte';
    public $timestamps = false;

    protected $fillable = [
        'idAuditorialInterna',
        'fechaGeneracion',
        'hallazgo',
        'oportunidadesMejora',
        'cantidadAuditoria',
        'ruta'
    ];

    public function auditoriaInterna()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna', 'idAuditorialInterna');
    }
}
