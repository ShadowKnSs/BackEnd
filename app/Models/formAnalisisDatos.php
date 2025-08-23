<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo FormAnalisisDatos
 * 
 * Representa el formulario general de análisis de datos asociado a un registro de proceso.
 * Permite vincular un periodo de evaluación a un conjunto de interpretaciones y necesidades (NeceInter).
 * 
 * Funcionalidades clave:
 * - Pertenece a un `Registro`.
 * - Se utiliza como base para el análisis de indicadores y resultados.
 */
class FormAnalisisDatos extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'formAnalisisDatos';

    // Clave primaria personalizada
    protected $primaryKey = 'id_formAnalisisDatos';

    // Campos que pueden asignarse masivamente
    protected $fillable = [
        'idRegistro',
        'periodoEva',
    ];

    /**
     * Relación: este formulario pertenece a un registro de proceso.
     */
    public function registro()
    {
        return $this->belongsTo(Registros::class, 'idRegistro', 'idRegistro');
    }
}
