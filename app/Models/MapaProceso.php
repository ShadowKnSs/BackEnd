<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapaProceso extends Model {
    use HasFactory;

    protected $table = 'mapaproceso';
    protected $primaryKey = 'idMapaProceso';
    public $timestamps = false;

    protected $fillable = [
        'idProceso', 
        'documentos', 
        'fuente', 
        'material', 
        'requisitos', 
        'salidas', 
        'receptores', 
        'puestosInvolucrados'
    ];

    // Relación con Procesos
    public function proceso() {
        return $this->belongsTo(Proceso::class, 'idProceso', 'idProceso');
    }
}
