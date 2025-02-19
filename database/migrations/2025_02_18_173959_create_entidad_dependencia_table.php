<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entidad_dependencia', function (Blueprint $table) {
            $table->increments('idEntidadDependecia');
            $table->string('nombreEntidadDependencia', 255); //Se refiera  a nombre (Facultada, Laboratorio)
            $table->string('ubicacion', 255); //Se refiere a dirección (Facultada, Laboratorio)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidad_dependencia');
    }
};
