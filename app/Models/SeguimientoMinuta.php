<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoMinuta extends Model
{
    protected $table = 'seguimientominuta';
    protected $primaryKey = 'idSeguimiento';
    protected $fillable = ['idRegistro', 'lugar', 'fecha', 'duracion'];

    public function registro()
    {
        return $this->belongsTo(Registro::class, 'idRegistro');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadMinuta::class, 'idSeguimiento');
    }

    public function asistentes()
    {
        return $this->hasMany(Asistente::class, 'idSeguimiento');
    }

    public function compromisos()
    {
        return $this->hasMany(CompromisoMinuta::class, 'idSeguimiento');
    }
    
    public $timestamps = false;
}
