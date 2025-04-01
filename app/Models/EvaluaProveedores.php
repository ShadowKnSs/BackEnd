<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluaProveedores extends Model
{
    use HasFactory;

    protected $table = 'evaluaProveedores';
    protected $primaryKey = 'idEvaProveedores';
    public $timestamps = false;

    protected $fillable = [
        'idIndicador',
        'confiable',
        'noConfiable',
        'condicionado',
        'noConfiable',
        'idformAnalisisDatos',
        'necesidad',
        'interpretacion'
    ];
    public function formAnalisisDatos()
    {
        return $this->belongsTo(formAnalisisDatos::class, 'id_formAnalisisDatos', 'idformAnalisisDatos');
    }
}
