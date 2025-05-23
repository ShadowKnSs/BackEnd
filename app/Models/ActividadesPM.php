<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadesPM extends Model
{
    protected $table = 'actividadespm';
    protected $primaryKey = 'idActividadPM';
    public $timestamps = false;

    protected $fillable = [
        'idProyectoMejora',
        'descripcionAct',
        'responsable',
        'fecha',
    ];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}

