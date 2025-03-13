<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('actividadcontrol', function (Blueprint $table) {
            $table->id('idActividad'); // Primary Key
            $table->unsignedBigInteger('idProceso');
            $table->unsignedBigInteger('idFormulario');
            $table->unsignedBigInteger('idResponsable');
            $table->string('nombreActividad', 255);
            $table->string('procedimiento', 255);
            $table->text('caracteristicasVerificar');
            $table->text('criterioAceptacion');
            $table->string('frecuencia', 255);
            $table->text('identificacionSalida');
            $table->text('registroSalida');
            $table->text('tratameinto'); // Posible error en el nombre
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividadcontrol');
    }
};
