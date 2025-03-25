<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuntosmejoraTable extends Migration
{
    public function up()
    {
        Schema::create('puntosmejora', function (Blueprint $table) {
            $table->id('idPunto');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->string('reqISO', 100);
            $table->string('descripcion', 255);
            $table->string('evidencia', 255);

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('puntosmejora');
    }
}
