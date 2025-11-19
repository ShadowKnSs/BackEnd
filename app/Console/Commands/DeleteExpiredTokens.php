<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TokenTemporal;
use Illuminate\Support\Carbon;

class DeleteExpiredTokens extends Command
{
    protected $signature = 'tokens:delete-expired';
    protected $description = 'Elimina automáticamente tokens temporales expirados';

    public function handle()
    {
        $count = TokenTemporal::where('expiracion', '<', Carbon::now())->delete();
        
        $this->info("Se eliminaron {$count} tokens expirados.");
        
        \Log::info("Tokens eliminados automáticamente: {$count}");
    }
}