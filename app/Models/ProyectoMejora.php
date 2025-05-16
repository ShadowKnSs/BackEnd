<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ProyectoMejora
 * 
 * Representa un proyecto de mejora completo dentro del sistema de gestión de calidad.
 * Cada proyecto se deriva de una actividad de mejora e incluye información sobre el área impactada,
 * responsables, recursos, objetivos, indicadores de éxito y actividades específicas.
 * 
 * Funcionalidades clave:
 * - Se relaciona con una `ActividadMejora` (a través de `idActividadMejora`).
 * - Tiene relaciones con: `Objetivo`, `IndicadoresExito`, `Recurso`, `ActividadesPM`, `ResponsableInv`.
 */
class ProyectoMejora extends Model
{
    // Tabla correspondiente en la base de datos
    protected $table = 'proyectomejora';

    // Clave primaria personalizada
    protected $primaryKey = 'idProyectoMejora';

    // No utiliza timestamps automáticos
    public $timestamps = false;

    // Campos asignables en masa
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

    /**
     * Objetivos definidos dentro del proyecto.
     */
    public function objetivos()
    {
        return $this->hasMany(Objetivo::class, 'idProyectoMejora');
    }

    /**
     * Indicadores de éxito vinculados al proyecto.
     */
    public function indicadoresExito()
    {
        return $this->hasMany(IndicadoresExito::class, 'idProyectoMejora');
    }

    /**
     * Recursos humanos, materiales y económicos asociados.
     */
    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'idProyectoMejora');
    }

    /**
     * Actividades planificadas para ejecutar la mejora.
     */
    public function actividades()
    {
        return $this->hasMany(ActividadesPM::class, 'idProyectoMejora');
    }

    /**
     * Responsables involucrados en la mejora.
     */
    public function responsablesInv()
    {
        return $this->hasMany(ResponsableInv::class, 'idProyectoMejora');
    }
}
