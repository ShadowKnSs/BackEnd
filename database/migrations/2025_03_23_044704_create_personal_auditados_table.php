<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalauditadoTable extends Migration
{
    public function up()
    {
        Schema::create('personalauditado', function (Blueprint $table) {
            $table->id('idPersonalAud');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->string('nombre', 255);
            $table->string('cargo', 255);

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('personalauditado');
    }
}
