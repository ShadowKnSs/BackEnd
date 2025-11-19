<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunBackupCommand extends Command
{
    protected $signature = 'sgc:backup';
    protected $description = 'Ejecuta el respaldo completo del sistema (Spatie Backup)';

    public function handle()
    {
        $this->info('Iniciando respaldo...');

        try {
            Artisan::call('backup:run');
            $output = Artisan::output();

            // Mostrar en consola
            $this->info($output);

            // Log
            Log::info('[SGC] Respaldo ejecutado correctamente.', [
                'output' => $output
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error ejecutando respaldo: " . $e->getMessage());
            Log::error('[SGC] Error en respaldo', ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
