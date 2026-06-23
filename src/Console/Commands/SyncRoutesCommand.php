<?php

namespace HenryHt\BitwisePermission\Console\Commands;

use HenryHt\BitwisePermission\Models\AppRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * bwp:sync-routes
 *
 * Escanea todas las rutas nombradas del proyecto y registra
 * automáticamente las wildcards en bwp_app_routes.
 *
 * Ejemplo:
 *   leads.index, leads.show, leads.store → leads.*
 */
class SyncRoutesCommand extends Command
{
    protected $signature   = 'bwp:sync-routes
                                {--dry-run : Solo mostrar rutas detectadas, sin guardar}
                                {--type=web : Tipo de ruta a registrar (web|api)}';

    protected $description = 'Sincroniza las rutas nombradas del proyecto con bwp_app_routes';

    public function handle(): int
    {
        $this->info('');
        $this->info('  Escaneando rutas del proyecto...');

        $routes    = Route::getRoutes();
        $wildcards = [];
        $type      = $this->option('type');

        foreach ($routes as $route) {
            $name = $route->getName();

            if (! $name) {
                continue;
            }

            // Ignorar rutas del propio paquete
            if (str_starts_with($name, 'bwp.')) {
                continue;
            }

            // Ignorar rutas de Laravel (debugbar, horizon, etc.)
            if (str_starts_with($name, '_') || str_starts_with($name, 'debugbar')) {
                continue;
            }

            // Convertir a wildcard
            $parts = explode('.', $name);
            if (count($parts) < 2) {
                continue;
            }

            $parts[count($parts) - 1] = '*';
            $wildcard = implode('.', $parts);

            if (! in_array($wildcard, $wildcards)) {
                $wildcards[] = $wildcard;
            }
        }

        if (empty($wildcards)) {
            $this->warn('  No se encontraron rutas nombradas.');
            return self::SUCCESS;
        }

        // Mostrar tabla de wildcards detectadas
        $this->table(
            ['Wildcard', 'Estado'],
            array_map(function ($w) {
                $exists = AppRoute::where('name', $w)->exists();
                return [$w, $exists ? '<fg=yellow>ya existe</>' : '<fg=green>nueva</>'];
            }, $wildcards)
        );

        if ($this->option('dry-run')) {
            $this->info('  Modo dry-run: no se guardó nada.');
            return self::SUCCESS;
        }

        if (! $this->confirm('  ¿Registrar estas rutas en bwp_app_routes?', true)) {
            return self::SUCCESS;
        }

        $created = 0;
        foreach ($wildcards as $wildcard) {
            AppRoute::firstOrCreate(
                ['name' => $wildcard],
                ['type' => $type, 'description' => '']
            );
            $created++;
        }

        $this->info("  <fg=green>✓</> {$created} rutas procesadas.");
        $this->info('');

        return self::SUCCESS;
    }
}
