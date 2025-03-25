<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EquipoAuditor extends Model
{
    use HasFactory;

    protected $table = 'equipoauditor';
    protected $primaryKey = 'idEquipoAud';
    public $timestamps = false;

    protected $fillable = ['idAuditorialInterna', 'rolAsignado', 'nombreAuditor', 'esAuditorLider'];

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
