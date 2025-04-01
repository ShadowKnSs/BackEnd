<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanTrabajo extends Model
{
    protected $table = 'plantrabajo';
    protected $primaryKey = 'idPlanTrabajo';
    public $timestamps = false; // se usarán created_at y updated_at

    protected $fillable = [
        'idActividadMejora',
        'responsable',
        'fechaElaboracion',
        'objetivo',
        'fechaRevision',
        'revisadoPor', 
        'estado',
        'fuente',
        'entregable'
        
    ];

    // Cada plan de trabajo pertenece a una actividad de mejora
    public function actividadMejora()
    {
        return $this->belongsTo(ActividadMejora::class, 'idActividadMejora', 'idActividadMejora');
    }

    // Un plan de trabajo tiene muchas fuentes (registros de la tabla fuentept)
    public function fuentes()
    {
        return $this->hasMany(FuentePt::class, 'idPlanTrabajo', 'idPlanTrabajo');
    }
}
