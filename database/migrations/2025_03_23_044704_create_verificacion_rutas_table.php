<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificacionrutaTable extends Migration
{
    public function up()
    {
        Schema::create('verificacionruta', function (Blueprint $table) {
            $table->id('idCriterio');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->string('criterio', 255);
            $table->string('reqAsociado', 255);
            $table->string('observaciones', 255);
            $table->string('evidencia', 255);
            $table->string('tipoHallazgo', 255);

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('verificacionruta');
    }
}
