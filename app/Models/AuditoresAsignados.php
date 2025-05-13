<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditoresAsignados extends Model
{
    use HasFactory;

    protected $table = 'auditoresasignados';
    public $timestamps = false;

    protected $primaryKey = 'idAsignacion';
    public $incrementing = true;

    protected $fillable = [
        'idAuditoria',
        'nombreAuditor'
    ];

    public function auditoria()
    {
        return $this->belongsTo(Cronograma::class, 'idAuditoria', 'idAuditoria');
    }
}