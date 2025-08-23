<?php
// app/Policies/UsuarioPolicy.php
namespace App\Policies;

use App\Models\Usuario;

class UsuarioPolicy
{
    public function delete(?Usuario $actor, Usuario $target): bool
    {
        if (!$actor) return false;
        // No puedes eliminarte a ti mismo
        if ($actor->idUsuario === $target->idUsuario) return false;
        // Si quieres restringir a Admin, descomenta:
        // return $actor->roles()->where('nombreRol','Administrador')->exists();
        return true;
    }
}
