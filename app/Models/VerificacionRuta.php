<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VerificacionRuta extends Model
{
    use HasFactory;

    protected $table = 'verificacionruta';
    protected $primaryKey = 'idCriterio';
    public $timestamps = false;

    protected $fillable = ['idAuditorialInterna', 'criterio', 'reqAsociado', 'observaciones', 'evidencia', 'tipoHallazgo'];

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
