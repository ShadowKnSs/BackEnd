<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadMinuta extends Model
{
    protected $table = 'actividadesminuta';
    protected $primaryKey = 'idActividadMin';
    protected $fillable = ['idSeguimiento', 'descripcion'];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoMinuta::class, 'idSeguimiento');
    }
    public $timestamps = false;
}
