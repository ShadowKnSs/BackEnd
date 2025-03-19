<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ControlCambio extends Model
{
    use HasFactory;

    protected $table = 'controlcambios';
    protected $primaryKey = 'idCambio';

    protected $fillable = [
        'idProceso',
        'idArchivo',
        'seccion',
        'edicion',
        'version',
        'fechaRevision',
        'descripcion'
    ];

    public function getFechaRevisionAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d h:i A');
    }

    public function setFechaRevisionAttribute($value)
    {
        $this->attributes['fechaRevision'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public $timestamps = false;
}
