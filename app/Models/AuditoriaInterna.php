<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditoriaInterna extends Model
{
    use HasFactory;

    protected $table = 'auditoriainterna';
    protected $primaryKey = 'idAuditorialInterna';
    public $timestamps = false;

    protected $fillable = [
        'idRegistro', 'fecha', 'objetivoAud', 'alcanceAud', 'fortalezas', 'debilidades',
        'gradoConformidad', 'gradoCumplimiento', 'mantenimientos', 'opinion',
        'fechaElabora', 'fechaRevisa', 'fechaAceptacion',
        'estadoElabora', 'estadoRevisa', 'estadoAceptacion',
        'conclusionesGenerales', 'observaciones', 'plazosConsideraciones', 'auditorLider'
    ];

    public function equipoAuditor()
    {
        return $this->hasMany(EquipoAuditor::class, 'idAuditorialInterna');
    }

    public function personalAuditado()
    {
        return $this->hasMany(PersonalAuditado::class, 'idAuditorialInterna');
    }

    public function verificacionRuta()
    {
        return $this->hasMany(VerificacionRuta::class, 'idAuditorialInterna');
    }

    public function puntosMejora()
    {
        return $this->hasMany(PuntosMejora::class, 'idAuditorialInterna');
    }

    public function criterios()
    {
        return $this->hasMany(CriteriosAuditoria::class, 'idAuditorialInterna');
    }

    public function conclusiones()
    {
        return $this->hasMany(ConclusionesGenerales::class, 'idAuditoriaInterna');
    }
    
    public function plazos()
    {
        return $this->hasMany(Plazo::class, 'idAuditorialInterna');
    }

}

