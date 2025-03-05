<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Noticia extends Model
{
    use HasFactory;

    protected $table = 'noticias';
    protected $primaryKey = 'idNoticias';

    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'titulo',
        'descripcion',
        'fechaPublicacion',
        'rutaImg'
    ];

    protected $casts = [
        'fechaPublicacion' => 'datetime',
    ];

    protected $appends = ['fechaPublicacionFormatted'];

    public function getFechaPublicacionFormattedAttribute(){
        if($this->fechaPublicacion){
            return $this->fechaPublicacion->format('d/m/Y H:i');
        }
        return null;
    }
}
