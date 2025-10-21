<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo GestionRiesgos
 * 
 * Representa la sección de gestión de riesgos dentro del análisis de un proceso.
 * Registra la persona responsable y la fecha de elaboración de la matriz de riesgos.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `Registro` de proceso.
 * - Se relaciona indirectamente con múltiples `Riesgo` (no definido aquí, pero esperado en una relación complementaria).
 */
class GestionRiesgos extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'gestionriesgos';

    // Clave primaria personalizada
    protected $primaryKey = 'idGesRies';

    // No se utilizan timestamps automáticos
    public $timestamps = false;

 protected $casts = [
        'fechaelaboracion' => 'date:Y-m-d',
    ];
    // Especificamos los campos que se pueden asignar masivamente
    protected $fillable = [
        'idRegistro',
        'elaboro',
        'fechaelaboracion',
    ];

    /**
     * Relación: esta gestión de riesgos pertenece a un registro de análisis de proceso.
     */
    public function registro()
    {
        return $this->belongsTo(Registros::class, 'idRegistro', 'idRegistro');
    }

    public function riesgos() {
        return $this->hasMany(Riesgo::class, 'idGesRies', 'idGesRies');
    }
}
