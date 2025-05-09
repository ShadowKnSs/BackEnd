<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NeceInter extends Model
{
    use HasFactory;

    protected $table = 'NeceInter'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'idNeceInter';

    public $timestamps = false; // Desactiva timestamps si la tabla no tiene created_at y updated_at

    protected $fillable = [
        'idformAnalisisDatos',
        'necesidad',
        'interpretacion',
        'seccion'
    ];

    // Relación: cada necesidad e interpretación pertenece a un formAnalisisDatos
    public function formAnalisisDatos()
    {
        return $this->belongsTo(FormAnalisisDatos::class, 'idformAnalisisDatos', 'idformAnalisisDatos');
    }
}
