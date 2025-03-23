<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConclusionesGenerales extends Model
{
    use HasFactory;

    protected $table = 'ConclusionesGenerales';
    protected $primaryKey = 'idConclusion';

    protected $fillable = [
        'idAuditoriaInterna',
        'nombre',
        'descripcionConclusion',
    ];

    public $timestamps = false;

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInterna::class, 'idAuditoriaInterna');
    }
}
