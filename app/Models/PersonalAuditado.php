<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalAuditado extends Model
{
    use HasFactory;

    protected $table = 'personalauditado';
    protected $primaryKey = 'idPersonalAud';
    public $timestamps = false;

    protected $fillable = ['idAuditorialInterna', 'nombre', 'cargo'];

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
