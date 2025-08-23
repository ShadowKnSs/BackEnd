<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Policies\UsuarioPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;


class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Usuario::class => UsuarioPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Definir política manualmente si es necesario
        Gate::define('delete-usuario', function ($user, $targetUser) {
            return $user->idUsuario !== $targetUser->idUsuario;
        });
    }
}
