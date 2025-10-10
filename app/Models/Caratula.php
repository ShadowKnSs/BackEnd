<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caratula extends Model
{
    use HasFactory;

    protected $table = 'caratula';
    protected $primaryKey = 'idCaratula';

    protected $fillable = [
        'idProceso',
        'version',
        'responsable_nombre',
        'responsable_cargo',
        'reviso_nombre',
        'reviso_cargo',
        'aprobo_nombre',
        'aprobo_cargo',
    ];

    public $timestamps = true;

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'idProceso');
    }
}
