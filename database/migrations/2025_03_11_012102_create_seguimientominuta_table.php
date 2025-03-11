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
    if (!Schema::hasTable('seguimientominuta')) {
        Schema::create('seguimientominuta', function (Blueprint $table) {
            $table->id('idSeguimiento');
            $table->unsignedBigInteger('idRegistro');
            $table->string('lugar');
            $table->date('fecha');
            $table->integer('duracion');
            $table->foreign('idRegistro')->references('idRegistro')->on('registro')->onDelete('cascade');
            $table->timestamps();
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientominuta');
    }
};
