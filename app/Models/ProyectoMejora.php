<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoMejora extends Model
{
    protected $table = 'proyectomejora';
    protected $primaryKey = 'idProyectoMejora';
    public $timestamps = false;

    protected $fillable = [
        'idActividadMejora',
        'division',                 
        'departamento',             
        'responsable',
        'fecha',
        'noMejora',
        'descripcionMejora',
        'areaImpacto',
        'personalBeneficiado',
        'situacionActual',
        'aprobacionNombre',
        'aprobacionPuesto'
    ];

    // Relaciones
    public function objetivos()
    {
        return $this->hasMany(Objetivo::class, 'idProyectoMejora');
    }

    public function indicadoresExito()
    {
        return $this->hasMany(IndicadoresExito::class, 'idProyectoMejora');
    }

    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'idProyectoMejora');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadesPM::class, 'idProyectoMejora');
    }

    public function responsablesInv()
    {
        return $this->hasMany(ResponsableInv::class, 'idProyectoMejora');
    }
}
