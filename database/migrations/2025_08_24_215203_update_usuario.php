<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Eliminar la constraint
        DB::statement('ALTER TABLE proceso DROP FOREIGN KEY fk_proceso_usuario');

        // 2. Modificar la columna
        DB::statement('ALTER TABLE proceso MODIFY idUsuario BIGINT UNSIGNED NULL');

        // 3. Crear nueva constraint
        DB::statement('
        ALTER TABLE proceso 
        ADD CONSTRAINT fk_proceso_usuario 
        FOREIGN KEY (idUsuario) 
        REFERENCES usuario(idUsuario) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
    ');
    }

    public function down()
    {
        DB::statement('ALTER TABLE proceso DROP FOREIGN KEY fk_proceso_usuario');

        DB::statement('ALTER TABLE proceso MODIFY idUsuario BIGINT UNSIGNED NOT NULL');

        DB::statement('
        ALTER TABLE proceso 
        ADD CONSTRAINT fk_proceso_usuario 
        FOREIGN KEY (idUsuario) 
        REFERENCES usuario(idUsuario) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
    ');
    }
};
