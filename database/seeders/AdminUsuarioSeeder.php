<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\TipoUsuario;

class AdminUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Asegura que exista el rol "Administrador" (id 1 según tu script)
        $adminRol = TipoUsuario::firstOrCreate(
            ['idTipoUsuario' => 1],
            [
                'nombreRol' => 'Administrador',
                'descripcion' => 'Responsable de la gestión global del sistema, administración de usuarios y control de permisos.',
            ]
        );

        // Crea el usuario admin si no existe por correo o RPE
        $admin = Usuario::firstOrCreate(
            ['correo' => 'admin@oniraweb.com.mx'], // puedes usar 'RPE' si prefieres
            [
                'nombre' => 'Administrador',
                'apellidoPat' => 'Sistema',
                'apellidoMat' => null,
                'telefono' => null,
                'gradoAcademico' => null,
                'RPE' => '280303',
                'pass' => Hash::make('Admin123!'),
                'activo' => 1,
            ]
        );

        // Vincula rol si no está ya vinculado
        $yaTiene = DB::table('usuario_tipo')
            ->where('idUsuario', $admin->idUsuario)
            ->where('idTipoUsuario', $adminRol->idTipoUsuario)
            ->exists();

        if (!$yaTiene) {
            DB::table('usuario_tipo')->insert([
                'idUsuario' => $admin->idUsuario,
                'idTipoUsuario' => $adminRol->idTipoUsuario,
            ]);
        }
    }
}