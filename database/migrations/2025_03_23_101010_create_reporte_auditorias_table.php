<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportesauditoriaTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reportesauditoria', function (Blueprint $table) {
            $table->id('idReporte');
            $table->unsignedBigInteger('idAuditorialInterna');
            $table->timestamp('fechaGeneracion')->useCurrent();
            $table->string('hallazgo');
            $table->string('oportunidadesMejora');
            $table->integer('cantidadAuditoria');
            $table->string('ruta');

            $table->foreign('idAuditorialInterna')
                  ->references('idAuditorialInterna')
                  ->on('auditoriainterna')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportesauditoria');
    }
}
