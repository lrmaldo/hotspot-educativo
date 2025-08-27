<?php

namespace App\Console\Commands;

use App\Services\MikrotikHotspotService;
use Illuminate\Console\Command;

class HotspotCleanup extends Command
{
    protected $signature = 'hotspot:cleanup {--dry : Solo mostrar sin borrar}';
    protected $description = 'Elimina usuarios hotspot expirados en Mikrotik';

    public function handle(MikrotikHotspotService $mikrotik): int
    {
        try {
            $removed = $mikrotik->cleanupExpired($this->option('dry'));
            $this->info('Usuarios eliminados: '.count($removed));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
