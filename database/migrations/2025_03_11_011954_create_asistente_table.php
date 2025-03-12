<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    if (!Schema::hasTable('asistente')) {
        Schema::create('asistente', function (Blueprint $table) {
            $table->id('idAsistente');
            $table->unsignedBigInteger('idSeguimiento');
            $table->string('nombre');
            $table->foreign('idSeguimiento')->references('idSeguimiento')->on('seguimientominuta')->onDelete('cascade');
            $table->timestamps();
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistente');
    }
};
