<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConclusionesGeneralesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conclusionesgenerales', function (Blueprint $table) {
            $table->id('idConclusion');
            $table->unsignedBigInteger('idAuditoriaInterna');
            $table->string('nombre', 255);
            $table->text('descripcionConclusion');
            $table->timestamps();

            $table->foreign('idAuditoriaInterna')
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
        Schema::dropIfExists('conclusionesgenerales');
    }
}
