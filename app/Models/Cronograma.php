<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cronograma extends Model
{
    use HasFactory;

    protected $table = 'auditorias';
    public $timestamps = false;

    protected $primaryKey = 'idAuditoria';
    public $incrementing = true;

    protected $fillable = [
        'idProceso',
        'fechaProgramada',
        'horaProgramada',
        'tipoAuditoria',
        'estado',
        'descripcion',
        'nombreProceso',
        'nombreEntidad'
    ];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }
}
