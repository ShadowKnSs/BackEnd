<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        if (!Schema::hasTable('Registros')) {
            Schema::create('Registros', function (Blueprint $table) {
                $table->id('idRegistro');
                $table->foreignId('idProceso')->constrained('proceso')->onDelete('cascade');
                $table->integer('año');
                $table->string('Apartado');
                $table->timestamps();
               
            });
        }
    }    

    public function down() {
        Schema::dropIfExists('Registros');
    }
};
