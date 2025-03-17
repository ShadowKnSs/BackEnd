<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanTrabajo extends Model
{
    protected $table = 'plantrabajo';
    protected $primaryKey = 'idPlanTrabajo';
    public $timestamps = true; // se usarán created_at y updated_at

    protected $fillable = [
        'idActividadMejora',
        'fechaElaboracion',
        'objetivo',
        'fechaRevision',
        'revisadoPor'
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
