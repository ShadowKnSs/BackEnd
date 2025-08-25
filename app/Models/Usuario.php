<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; // Agregar esta línea

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Agregar HasApiTokens aquí

    protected $table = 'usuario';

    // Clave primaria personalizada
    protected $primaryKey = 'idUsuario';

    // No se manejan timestamps automáticos
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'idTipoUsuario',
        'nombre',
        'apellidoPat',
        'apellidoMat',
        'telefono',
        'correo',
        'gradoAcademico',
        'activo',
        'fechaRegistro',
        'RPE',
        'pass'
    ];


    protected $casts = [
        'activo' => 'boolean',
        'fechaRegistro' => 'datetime',
    ];
    /**
     * Relación muchos a muchos con roles (usuario puede tener varios).
     */
    public function roles()
    {
        return $this->belongsToMany(TipoUsuario::class, 'usuario_tipo', 'idUsuario', 'idTipoUsuario');
    }

    public function procesos()
    {
        return $this->hasMany(Proceso::class, 'idUsuario');
    }

    /**
     * Procesos que el usuario supervisa.
     */
    public function procesosSupervisados()
    {
        return $this->hasMany(SupervisorProceso::class, 'idUsuario');
    }

    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('activo', 1);
        });
    }

    public function scopeWithInactive($query)
    {
        return $query->withoutGlobalScope('active');
    }

    // --- Scopes para filtros/búsqueda ---
    public function scopeBuscar($q, ?string $term)
    {
        if (!$term)
            return $q;
        $term = trim($term);
        return $q->where(function ($qq) use ($term) {
            $qq->where('nombre', 'like', "%{$term}%")
                ->orWhere('apellidoPat', 'like', "%{$term}%")
                ->orWhere('apellidoMat', 'like', "%{$term}%")
                ->orWhere('correo', 'like', "%{$term}%");
        });
    }

    public function scopeFiltrarRol($q, ?string $rol)
    {
        if (!$rol)
            return $q;
        return $q->whereHas('roles', fn($r) => $r->where('nombreRol', $rol));
    }


    public function scopeEstado($q, $estado)
    {
        if ($estado === 'true') {
            return $q->where('activo', 1);
        }
        if ($estado === 'false') {
            return $q->where('activo', 0);
        }
        return $q->withInactive();
    }
}