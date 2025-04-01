<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formatos extends Model
{
    use HasFactory;

    protected $table = 'Formatos';
    public $timestamps = false;

    protected $primaryKey = 'idFormato';
    public $incrementing = true;

    protected $fillable = [
        'idUsuario',
        'nombreFormato',
        'ruta',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
}

