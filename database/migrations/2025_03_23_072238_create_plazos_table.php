<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('plazos', function (Blueprint $table) {
            $table->id('idPlazo');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->string('descripcion', 255);
            $table->timestamps();

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plazos');
    }
};
