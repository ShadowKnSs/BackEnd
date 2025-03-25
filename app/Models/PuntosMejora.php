<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PuntosMejora extends Model
{
    use HasFactory;

    protected $table = 'puntosmejora';
    protected $primaryKey = 'idPunto';
    public $timestamps = false;

    protected $fillable = ['idAuditorialInterna', 'reqISO', 'descripcion', 'evidencia'];

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
