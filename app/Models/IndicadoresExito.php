<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicadoresExito extends Model
{
    protected $table = 'IndicadoresExito';
    protected $primaryKey = 'idIndicadorExito';
    public $timestamps = false;

    protected $fillable = [
        'idProyectoMejora',
        'nombreInd',
        'meta',
    ];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
