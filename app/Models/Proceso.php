<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Proceso
 * 
 * Representa un proceso certificado dentro del sistema de gestión de calidad.
 * Cada proceso está relacionado con un macroproceso, una entidad/dependencia y un líder de proceso (usuario).
 * 
 * Funcionalidades clave:
 * - Contiene datos administrativos y de certificación del proceso.
 * - Se relaciona con múltiples módulos: análisis, auditorías, cronogramas, etc.
 * - Tiene relaciones directas con `MacroProceso`, `Usuario` y `EntidadDependencia`.
 */
class Proceso extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'proceso';

    // Clave primaria personalizada
    protected $primaryKey = 'idProceso';

    // Campos que pueden asignarse masivamente
    protected $fillable = [
        'idMacroproceso',
        'idUsuario',
        'idEntidad',
        'nombreProceso',
        'objetivo',
        'alcance',
        'anioCertificado',
        'norma',
        'duracionCetificado',
        'estado',
        'icono'
    ];

    /**
     * Relación: este proceso pertenece a un macroproceso.
     */
    public function macroproceso()
    {
        return $this->belongsTo(MacroProceso::class, 'idMacroproceso', 'idMacroproceso');
    }

    /**
     * Relación: este proceso tiene un usuario responsable (líder de proceso).
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Relación: este proceso pertenece a una entidad o dependencia.
     */
    public function entidad()
    {
        return $this->belongsTo(EntidadDependencia::class, 'idEntidad', 'idEntidadDependencia');
    }
}
