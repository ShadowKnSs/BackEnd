<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForOptimization extends Migration
{
    public function up()
    {
        // Índices para la tabla usuario
        Schema::table('usuario', function (Blueprint $table) {
            $table->index('activo');
            $table->index('correo');
            $table->index('RPE');
        });

        // Índices para la tabla usuario_tipo
        Schema::table('usuario_tipo', function (Blueprint $table) {
            $table->index('idUsuario');
            $table->index('idTipoUsuario');
        });

        // Índices para la tabla supervisor_proceso
        Schema::table('supervisor_proceso', function (Blueprint $table) {
            $table->index('idUsuario');
            $table->index('idProceso');
        });
    }

    public function down()
    {
        // Eliminar índices de la tabla usuario
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropIndex(['activo']);
            $table->dropIndex(['correo']);
            $table->dropIndex(['RPE']);
        });

        // Eliminar índices de la tabla usuario_tipo
        Schema::table('usuario_tipo', function (Blueprint $table) {
            $table->dropIndex(['idUsuario']);
            $table->dropIndex(['idTipoUsuario']);
        });

        // Eliminar índices de la tabla supervisor_proceso
        Schema::table('supervisor_proceso', function (Blueprint $table) {
            $table->dropIndex(['idUsuario']);
            $table->dropIndex(['idProceso']);
        });
    }
}