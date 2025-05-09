<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $primaryKey = 'idDocumento';
    public $timestamps = false;

    protected $fillable = [
        'idProceso',
        'nombreDocumento',
        'codigoDocumento',
        'tipoDocumento',
        'fechaRevision',
        'fechaVersion',
        'noRevision',
        'noCopias',
        'tiempoRetencion',
        'lugarAlmacenamiento',
        'medioAlmacenamiento',
        'disposicion',
        'responsable',
    ];
    
}
