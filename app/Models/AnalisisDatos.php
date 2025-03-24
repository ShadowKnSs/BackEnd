<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisDatos extends Model
{
    use HasFactory;

    protected $table = 'analisisdatos'; // Ajusta el nombre según tu migración
    protected $primaryKey = 'idAnalisisDatoa';

    public $timestamps = false;
    protected $fillable = [
        'idRegistro',
        'interpretacion',
        'necesidad',
        'seccion'
    ];

    public function registro()
    {
        return $this->belongsTo(Registros::class, 'idRegistro', 'idRegistro');
    }
}
