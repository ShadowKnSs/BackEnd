<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluaProveedores extends Model
{
    use HasFactory;

    protected $table = 'evaluaproveedores'; // Ajusta el nombre si es necesario
    protected $primaryKey = 'idEvaProveedores';
    public $timestamps = false; // Si no usas timestamps

    protected $fillable = [
        'idIndicador',
        'confiable',
        'condicionado',
        'noConfiable'
    ];
}
