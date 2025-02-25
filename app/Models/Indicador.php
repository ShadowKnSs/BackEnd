<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Indicador extends Model
{
    use HasFactory;

    protected $table = 'analiticasdatos'; // Asegúrate de que coincida con el nombre de la tabla en la BD

    // Define los campos asignables
    protected $fillable = [
        'tipo',            // Enum: 'Encuesta de Satisfacción', 'Retroalimentación', 'Evaluación de proveedores', 'Plan de control', 'Mapa de proceso', 'Gestión de Riesgos'
        'nombre',          // Nombre o descripción del indicador
        'periodo',         // 'Semestral' o 'Anual'
        'meta',            // Meta del indicador (puede ser numérico o texto)
        'estado_color',    // Estado: inicialmente vacío, se asigna "amarillo" en la primera evaluación semestral y "verde" cuando se completa, o verde directamente en indicadores anuales
    ];

    // Relaciones opcionales según el tipo
    public function evaluacionProveedor()
    {
        return $this->hasMany(EvaluacionProveedor::class, 'indicador_id');
    }

    public function actividadControl()
    {
        return $this->hasMany(ActividadControl::class, 'indicador_id');
    }

    public function mapaProceso()
    {
        return $this->hasMany(IndMapaProceso::class, 'indicador_id');
    }

    public function gestionRiesgo()
    {
        return $this->hasMany(GestionRiesgo::class, 'indicador_id');
    }
}
