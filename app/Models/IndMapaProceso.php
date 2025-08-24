<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo IndMapaProceso
 * 
 * Representa los indicadores derivados del mapa de proceso.
 * Cada indicador está asociado a un proceso específico e incluye su descripción, fórmula,
 * meta esperada, periodo de medición y responsable del seguimiento.
 * 
 * Funcionalidades clave:
 * - No usa timestamps automáticos.
 * - Permite registrar y consultar los indicadores clave definidos desde el mapa de proceso.
 */
class IndMapaProceso extends Model
{
    use HasFactory;

    // No utiliza campos created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'indmapaproceso';

    // Clave primaria personalizada
    protected $primaryKey = 'idIndicadorMP';

    // Atributos que pueden ser asignados masivamente
    protected $fillable = [
        'idProceso',
        'descripcion',
        'formula',
        'periodoMed',
        'responsable',
        'meta'
    ];
}
