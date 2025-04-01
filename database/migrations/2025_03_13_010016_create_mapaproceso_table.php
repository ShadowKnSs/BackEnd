<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('mapaproceso')) {
            Schema::create('mapaproceso', function (Blueprint $table) {
                $table->id('idMapaProceso'); // Clave primaria
                $table->unsignedBigInteger('idProceso'); // Clave foránea a procesos
                $table->text('documentos')->nullable();
                $table->text('fuente')->nullable();
                $table->text('material')->nullable();
                $table->text('requisitos')->nullable();
                $table->text('salidas')->nullable();
                $table->text('receptores')->nullable();
                $table->text('puestosInvolucrados')->nullable();
                $table->timestamps();

                // Clave foránea
                $table->foreign('idProceso')->references('idProceso')->on('procesos')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('mapaproceso');
    }
};

