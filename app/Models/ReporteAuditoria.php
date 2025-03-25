<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteAuditoria extends Model
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
        'ruta',
    ];

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
