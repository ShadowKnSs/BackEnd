<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsableInv extends Model
{
    protected $table = 'ResponsablesInvo';
    protected $primaryKey = 'idResponsableInvo';
    public $timestamps = false;

    protected $fillable = [
        'idProyectoMejora',
        'nombre'
    ];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoMejora::class, 'idProyectoMejora');
    }
}
