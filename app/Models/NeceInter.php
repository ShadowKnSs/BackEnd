<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo NeceInter
 * 
 * Representa la interpretación del comportamiento del proceso y la necesidad de mejora,
 * registradas por sección dentro del análisis de datos de un proceso.
 * 
 * Funcionalidades clave:
 * - Pertenece a un `FormAnalisisDatos`.
 * - Permite registrar la interpretación y necesidad por sección: Conformidad, Satisfacción, Desempeño, etc.
 */
class NeceInter extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'NeceInter';

    // Clave primaria personalizada
    protected $primaryKey = 'idNeceInter';

    // No utiliza timestamps automáticos
    public $timestamps = false;

    // Campos que pueden asignarse en masa
    protected $fillable = [
        'idAnalisisDatos',
        'Necesidad',
        'Interpretacion',
        'seccion'
    ];

    /**
     * Relación: esta necesidad e interpretación pertenece a un formulario de análisis de datos.
     */
    public function formAnalisisDatos()
    {
        return $this->belongsTo(FormAnalisisDatos::class, 'idAnalisisDatos', 'idAnalisisDatos');
    }
}
