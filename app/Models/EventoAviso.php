<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoAviso extends Model
{
    use HasFactory;

    protected $table = 'eventosavisos';
    protected $primaryKey = 'idEventosAvisos';

    protected $fillable = [
        'idUsario',
        'fechaPublicacion',
        'tipo',
        'rutaImg'
    ];

    protected $casts = [
        'fechaPublicacion' => 'datetime',
    ];

    // Al serializar en JSON, puedes mutar la fecha
    protected $appends = ['fechaPublicacionFormatted'];

    public function getFechaPublicacionFormattedAttribute()
    {
        if ($this->fechaPublicacion) {
            // Formato dd-mm-yyyy HH:mm
            return $this->fechaPublicacion->format('d-m-Y H:i');
        }
        return null;
    }
}
