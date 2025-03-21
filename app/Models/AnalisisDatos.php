<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisDatos extends Model
{
    use HasFactory;

    protected $table = 'analisisdatos'; 
    protected $primaryKey = 'idAnalisisDatos';

    public $timestamps = false;
    protected $fillable = [
        'idRegistro',
        'interpretacion',
        'necesidad'
    ];

    public function registro()
    {
        return $this->belongsTo(Registro::class, 'idRegistro', 'idRegistro');
    }
}
