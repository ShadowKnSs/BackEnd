<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipoauditorTable extends Migration
{
    public function up()
    {
        Schema::create('equipoauditor', function (Blueprint $table) {
            $table->id('idEquipoAud');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->string('rolAsignado', 255);
            $table->string('nombreAuditor', 255);
            $table->boolean('esAuditorLider')->default(false);

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipoauditor');
    }
}
