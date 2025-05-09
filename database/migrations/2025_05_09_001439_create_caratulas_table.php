<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaratulaTable extends Migration
{
    public function up()
    {
        Schema::create('caratula', function (Blueprint $table) {
            $table->id('idCaratula');
            $table->unsignedBigInteger('idProceso');

            $table->string('responsable_nombre');
            $table->string('responsable_cargo');
            $table->string('reviso_nombre');
            $table->string('reviso_cargo');
            $table->string('aprobo_nombre');
            $table->string('aprobo_cargo');

            $table->timestamps();

            $table->foreign('idProceso')->references('idProceso')->on('proceso')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('caratula');
    }
}
