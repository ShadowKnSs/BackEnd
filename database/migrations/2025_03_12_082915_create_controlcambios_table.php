<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('controlcambios', function (Blueprint $table) {
            $table->id('idCambio');
            $table->integer('idProceso');
            $table->integer('idArchivo');
            $table->string('seccion', 255);
            $table->integer('edicion');
            $table->integer('version');
            $table->timestamp('fechaRevision')->useCurrent();
            $table->text('descripcion');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('controlcambios');
    }
};
