<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditoriainternaTable extends Migration
{
    public function up()
    {
        Schema::create('auditoriainterna', function (Blueprint $table) {
            $table->id('idAuditorialInterna');
            $table->integer('idRegistro');
            $table->timestamp('fecha');
            $table->string('objetivoAud', 255);
            $table->string('alcanceAud', 255);
            $table->string('fortalezas', 255);
            $table->string('debilidades', 255);
            $table->float('gradoConformidad');
            $table->float('gradoCumplimiento');
            $table->string('mantenimientos', 255);
            $table->string('opinion', 255);
            $table->timestamp('fechaElabora')->nullable();
            $table->timestamp('fechaRevisa')->nullable();
            $table->timestamp('fechaAceptacion')->nullable();
            $table->string('estadoElabora', 100);
            $table->string('estadoRevisa', 100);
            $table->string('estadoAceptacion', 100);
            $table->text('conclusionesGenerales');
            $table->text('observaciones');
            $table->text('plazosConsideraciones');
            $table->string('auditorLider', 150);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoriainterna');
    }
}
