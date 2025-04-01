<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaInterna extends Model
{
    protected $table = 'auditoriainterna';

    protected $primaryKey = 'idAuditorialInterna';

    public $timestamps = false;

    protected $fillable = [
        'idRegistro',
        'fecha',
        'objetivoAud',
        'alcanceAud',
        'fortalezas',
        'debilidades',
        'gradoConformidad',
        'gradoCumplimiento',
        'mantenimientos',
        'opinion',
        'fechaElabora',
        'fechaRevisa',
        'fechaAceptacion',
        'estadoElabora',
        'estadoRevisa',
        'estadoAceptacion',
        'conclusionesGenerales',
        'observaciones',
        'plazosConsideraciones',
        'auditorLider'
    ];

    public function registro()
    {
        return $this->belongsTo(Registros::class, 'idRegistro', 'idRegistro');
    }
}

