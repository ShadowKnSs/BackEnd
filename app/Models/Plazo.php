<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plazo extends Model
{
    use HasFactory;

    protected $table = 'plazos';
    protected $primaryKey = 'idPlazo';

    protected $fillable = [
        'idAuditorialInterna',
        'descripcion'
    ];

    public $timestamps = false;

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditorialInterna');
    }
}
