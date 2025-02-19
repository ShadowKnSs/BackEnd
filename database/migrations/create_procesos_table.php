<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Se definen los campos de la tabla
        Schema::create('proceso', function (Blueprint $table) {
            $table->increments('idProceso');
            $table->unsignedInteger('idMacroproceso');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedInteger('idEntidad');
            $table->string('nombreProceso', 255);
            $table->text('objetivo');
            $table->text('alcance');
            $table->integer('anioCertificacion');
            $table->string('norma', 255);
            $table->integer('duracionCertificado');
            $table->enum('estado', ['Activo', 'Inactivo']);
            $table->timestamps();

            //Definimos la clave foránea con la tabla Macroprocesos
            $table->foreign('idMacroproceso')->references('idMacroproceso')->on('macroproceso')->onDelete('cascade');
            //Definimos la clave foránea con la tabla Usuarios
            $table->foreign('idUsuario')->references('idUsuario')->on('usuario')->onDelete('cascade');
            //Definimos la clave foránea con la tabla EntidadesoDependencias
            $table->foreign('idEntidad')->references('idEntidadDependecia')->on('entidad_dependencia')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso');

    }
};
