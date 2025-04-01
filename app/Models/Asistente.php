<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistente extends Model
{
    protected $table = 'asistente';
    protected $primaryKey = 'idAsistente';
    protected $fillable = ['idSeguimiento', 'nombre'];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoMinuta::class, 'idSeguimiento');
    }
    public $timestamps = false;
}
