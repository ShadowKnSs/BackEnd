<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo SeguimientoMinuta
 * 
 * Representa una minuta de seguimiento asociada a un registro del sistema.
 * Almacena datos del lugar, fecha y duración de la reunión.
 * 
 * Funcionalidades clave:
 * - Se relaciona con:
 *   - `Registros` (registro principal del proceso)
 *   - `ActividadMinuta` (actividades tratadas)
 *   - `Asistente` (personas presentes)
 *   - `CompromisoMinuta` (acuerdos o compromisos establecidos)
 */
class SeguimientoMinuta extends Model
{
    // Nombre de la tabla
    protected $table = 'seguimientominuta';

    // Clave primaria
    protected $primaryKey = 'idSeguimiento';

    // Atributos que pueden asignarse masivamente
    protected $fillable = ['idRegistro', 'lugar', 'fecha', 'duracion'];

    // No se utilizan timestamps automáticos
    public $timestamps = false;

    /**
     * Registro principal relacionado a esta minuta.
     */
    public function registro()
    {
        return $this->belongsTo(Registros::class, 'idRegistro');
    }

    /**
     * Actividades documentadas en esta minuta.
     */
    public function actividades()
    {
        return $this->hasMany(ActividadMinuta::class, 'idSeguimiento');
    }

    /**
     * Asistentes presentes en la reunión.
     */
    public function asistentes()
    {
        return $this->hasMany(Asistente::class, 'idSeguimiento');
    }

    /**
     * Compromisos o acuerdos derivados de la reunión.
     */
    public function compromisos()
    {
        return $this->hasMany(CompromisoMinuta::class, 'idSeguimiento');
    }
}
