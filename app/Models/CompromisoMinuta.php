<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompromisoMinuta extends Model
{
    protected $table = 'compromisosminuta';
    protected $primaryKey = 'idCompromiso';
    protected $fillable = ['idSeguimiento', 'descripcion', 'responsables', 'fecha'];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoMinuta::class, 'idSeguimiento');
    }
    public $timestamps = false;
}
