<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriteriosAuditoria extends Model
{
    protected $table = 'criteriosauditoria';
    protected $primaryKey = 'idCriterio';
    public $timestamps = false;

    protected $fillable = [
        'idAuditorialInterna',
        'criterio'
    ];

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
