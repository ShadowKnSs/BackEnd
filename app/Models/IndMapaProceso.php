<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndMapaProceso extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'indmapaproceso'; // Nombre de la tabla
    protected $primaryKey = 'idIndicadorMP'; // Primary Key

    protected $fillable = [
        'idMapaProceso',
        'idResponsable',
        'idIndicador',
        'descripcion',
        'formula',
        'periodoMed'
    ];
}
