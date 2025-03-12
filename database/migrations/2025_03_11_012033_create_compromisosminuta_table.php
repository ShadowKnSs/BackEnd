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
    if (!Schema::hasTable('compromisosminuta')) {
        Schema::create('compromisosminuta', function (Blueprint $table) {
            $table->id('idCompromiso');
            $table->unsignedBigInteger('idSeguimiento');
            $table->text('descripcion');
            $table->text('responsables');
            $table->date('fecha');
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
        Schema::dropIfExists('compromisosminuta');
    }
};
