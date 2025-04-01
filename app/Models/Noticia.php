<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon; 


class Noticia extends Model
{
    use HasFactory;

    protected $table = 'Noticias';
    protected $primaryKey = 'idNoticias';

    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'titulo',
        'descripcion',
        'fechaPublicacion',
        'rutaImg'
    ];
  
    protected $dates = ['fechaPublicacion'];

    public function getFechaPublicacionAttribute($value)
    {
        // $value es el valor bruto (ej: "2025-03-05T18:38:05.000000Z")
        // Carbon::parse($value) convierte a Carbon
        return Carbon::parse($value)->format('d-m-Y H:i');
    }
}
