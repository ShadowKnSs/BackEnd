<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('indmapaproceso')) {
            Schema::create('indmapaproceso', function (Blueprint $table) {
                $table->id('idIndicadorMP'); // Clave primaria
                $table->integer('idMapaProceso');
                $table->integer('idResponsable');
                $table->integer('idIndicador');
                $table->text('descripcion')->nullable();
                $table->text('formula')->nullable();
                $table->string('periodoMed', 50)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('indmapaproceso');
    }
};

