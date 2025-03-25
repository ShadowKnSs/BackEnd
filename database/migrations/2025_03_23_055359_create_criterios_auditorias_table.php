<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriosauditoriaTable extends Migration
{
    public function up()
    {
        Schema::create('criteriosauditoria', function (Blueprint $table) {
            $table->id('idCriterio');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->string('criterio', 255);

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('criteriosauditoria');
    }
}
