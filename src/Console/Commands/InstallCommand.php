<?php

namespace HenryHt\BitwisePermission\Console\Commands;

use HenryHt\BitwisePermission\Database\Seeders\BitwisePermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature   = 'bwp:install
                                {--migrate : Ejecutar migraciones automáticamente}
                                {--seed    : Sembrar datos base automáticamente}
                                {--force   : Sobreescribir archivos existentes}';

    protected $description = 'Instala laravel-bitwise-permission en el proyecto';

    public function handle(): int
    {
        $this->info('');
        $this->info('  ██████╗ ██╗    ██╗██████╗ ');
        $this->info('  ██╔══██╗██║    ██║██╔══██╗');
        $this->info('  ██████╔╝██║ █╗ ██║██████╔╝');
        $this->info('  ██╔══██╗██║███╗██║██╔═══╝ ');
        $this->info('  ██████╔╝╚███╔███╔╝██║     ');
        $this->info('  ╚═════╝  ╚══╝╚══╝ ╚═╝     ');
        $this->info('  laravel-bitwise-permission  ');
        $this->info('');

        // 1. Publicar config
        $this->step('Publicando configuración...');
        Artisan::call('vendor:publish', [
            '--tag'   => 'bwp-config',
            '--force' => $this->option('force'),
        ]);
        $this->line('  <fg=green>✓</> config/bitwise-permission.php');

        // 2. Publicar migraciones
        $this->step('Publicando migraciones...');
        Artisan::call('vendor:publish', [
            '--tag'   => 'bwp-migrations',
            '--force' => $this->option('force'),
        ]);
        $this->line('  <fg=green>✓</> database/migrations/..._create_bitwise_permission_tables.php');

        // 3. Publicar assets CSS
        $this->step('Publicando assets...');
        Artisan::call('vendor:publish', [
            '--tag'   => 'bwp-assets',
            '--force' => $this->option('force'),
        ]);
        $this->line('  <fg=green>✓</> public/vendor/bitwise-permission/bwp.css');

        // 4. Ejecutar migraciones si se pidió
        if ($this->option('migrate') || $this->confirm('  ¿Ejecutar migraciones ahora?', true)) {
            $this->step('Ejecutando migraciones...');
            Artisan::call('migrate', [], $this->output);
            $this->line('  <fg=green>✓</> Migraciones ejecutadas');
        }

        // 5. Sembrar datos base si se pidió
        if ($this->option('seed') || $this->confirm('  ¿Sembrar datos base (permisos, roles, rutas)?', true)) {
            $this->step('Sembrando datos base...');
            Artisan::call('db:seed', [
                '--class' => BitwisePermissionSeeder::class,
            ], $this->output);
            $this->line('  <fg=green>✓</> Datos base sembrados');
        }

        // 6. Instrucciones finales
        $this->info('');
        $this->info('  <fg=green>¡Instalación completada!</>');
        $this->info('');
        $this->line('  Próximos pasos:');
        $this->line('  1. Agrega <fg=yellow>role_id</> (FK a bwp_roles) en tu tabla <fg=yellow>users</>');
        $this->line('  2. Incluye el trait en tu modelo User:');
        $this->line('     <fg=cyan>use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;</>');
        $this->line('');
        // $this->line('  3. Registra el middleware en bootstrap/app.php:');
        // $this->line('     <fg=cyan>->withMiddleware(function (Middleware $m) {</>');
        // $this->line('     <fg=cyan>    $m->alias([\'bwp.permission\' => CheckPermissionMiddleware::class]);</>');
        // $this->line('     <fg=cyan>})</>');
        // $this->line('');
        $this->line('  3. Accede a la UI en: <fg=cyan>/bwp/roles</>');
        $this->info('');

        return self::SUCCESS;
    }

    protected function step(string $message): void
    {
        $this->line('');
        $this->line("  <fg=blue>→</> {$message}");
    }
}
