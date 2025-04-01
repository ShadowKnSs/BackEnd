<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('proceso')) { // Verifica si la tabla ya existe
            Schema::create('proceso', function (Blueprint $table) {
                $table->id('idProceso'); // ID del proceso
                $table->foreignId('idMacroproceso')->constrained('macroprocesos')->onDelete('cascade'); // Relación con tabla macroprocesos
                $table->foreignId('idUsuario')->constrained('users')->onDelete('cascade'); // Relación con tabla users
                $table->foreignId('idEntidad')->constrained('entidades')->onDelete('cascade'); // Relación con tabla entidades
                $table->string('nombreProceso'); // Nombre del proceso
                $table->text('objetivo'); // Objetivo del proceso
                $table->text('alcance'); // Alcance del proceso
                $table->year('anioCertificado'); // Año del certificado
                $table->string('norma'); // Norma asociada al proceso
                $table->integer('duracionCetificado'); // Duración del certificado en años
                $table->string('estado'); // Estado del proceso (activo, inactivo, etc.)
                $table->timestamps(); // Para created_at y updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso');
    }
};
