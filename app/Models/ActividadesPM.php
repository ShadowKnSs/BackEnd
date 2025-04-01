<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadesPM extends Model
{
    protected $table = 'actividadespm';
    protected $primaryKey = 'idActividadPM';
    protected $fillable = [
        'idProyectoMejora',
        'descripcionAct',
        'responsable',
        'fecha',
    ];

    // Deshabilitar timestamps si la tabla no tiene created_at y updated_at
    public $timestamps = false;

}