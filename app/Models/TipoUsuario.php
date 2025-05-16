<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo TipoUsuario
 * 
 * Define los distintos tipos de roles disponibles en el sistema (ej. Admin, Líder de Proceso, Auditor, etc.).
 * Cada tipo de usuario puede estar relacionado con múltiples usuarios mediante `idTipoUsuario`.
 * 
 * Funcionalidades clave:
 * - Permite asignar roles a usuarios.
 * - Relación uno a muchos con el modelo Usuario.
 */
class TipoUsuario extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'tipousuario';

    // Clave primaria personalizada
    protected $primaryKey = 'idTipoUsuario';

    // Atributos que se pueden asignar en masa
    protected $fillable = [
        'nombreRol',
        'descripcion'
    ];

    /**
     * Relación uno a muchos: un tipo puede tener muchos usuarios asignados.
     */
    public function usuario(){
        return $this->hasMany(Usuario::class, 'idTipoUsuario', 'idTipoUsuario');
    }
}
