<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cronograma extends Model
{
    use HasFactory;

    protected $table = 'auditorias';
    public $timestamps = false;

    protected $primaryKey = 'idAuditoria';
    public $incrementing = true;

    protected $fillable = [
        'idProceso',
        'fechaProgramada',
        'horaProgramada',
        'tipoAuditoria',
        'estado',
        'descripcion',
    ];

     public function auditoresAsignados()
    {
        // especifica también la local key porque tu PK no es 'id'
        return $this->hasMany(AuditoresAsignados::class, 'idAuditoria', 'idAuditoria');
    }

    // Alias para no romper donde ya llamas ->asignados()
    public function asignados()
    {
        return $this->auditoresAsignados();
    }
}
