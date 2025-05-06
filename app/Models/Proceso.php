<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proceso extends Model
{
    // Indicamos la tabla en la base de datos donde se almacenan los datos
    protected $table = 'proceso';
    // Indicamos la clave primaria personalizada
    protected $primaryKey = 'idProceso';

     protected $fillable = [
         'idMacroproceso',
         'idUsuario',
         'idEntidad',
         'nombreProceso',
         'objetivo',
         'alcance',
         'anioCertificado',
         'norma',
         'duracionCetificado',
         'estado',
         'icono'
     ];
 
     // Relación con MacroProceso
     public function macroproceso()
     {
         return $this->belongsTo(MacroProceso::class, 'idMacroproceso', 'idMacroproceso');
     }
 
     // Relación con Usuario (asumiendo que usas el modelo User)
     public function usuario()
     {
         return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
     }
 
     // Relación con EntidadDependencia
     public function entidad()
     {
         return $this->belongsTo(EntidadDependencia::class, 'idEntidad', 'idEntidadDependencia');
     }
}
