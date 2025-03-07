<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; 

class EventoAviso extends Model
{
    use HasFactory;

    protected $table = 'eventosavisos';
    protected $primaryKey = 'idEventosAvisos';

    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'fechaPublicacion',
        'tipo',
        'rutaImg'
    ];

    
    public function getFechaPublicacionAttribute($value)
    {
        if (!$value) return null;
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i');
    }
}
