<?php

namespace HenryHt\BitwisePermission\Console\Commands;

use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBaseCommand extends Command
{
    protected $signature = 'bwp:sync-base
                            {--roles : Sincronizar base_roles desde config}
                            {--routes : Sincronizar base_routes desde config}
                            {--menus : Sincronizar base_menus desde config}';

    protected $description = 'Re-sincroniza base_routes, base_roles y base_menus desde config sin duplicar';

    public function handle(): int
    {
        $syncRoles  = $this->option('roles');
        $syncRoutes = $this->option('routes');
        $syncMenus  = $this->option('menus');

        // Si no se especifica ninguno, sincronizar todos
        if (! $syncRoles && ! $syncRoutes && ! $syncMenus) {
            $syncRoles  = true;
            $syncRoutes = true;
            $syncMenus  = true;
        }

        $this->info('');
        $this->info('  Sincronizando datos base desde config...');
        $this->info('');

        if ($syncRoles) {
            $this->syncRoles();
        }

        if ($syncRoutes) {
            $this->syncRoutes();
        }

        if ($syncMenus) {
            $this->syncMenus();
        }

        $this->info('');
        $this->info('  <fg=green>✓</> Sincronización completada.');
        $this->info('');

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────
    // Roles
    // ─────────────────────────────────────────────────────────
    protected function syncRoles(): void
    {
        $baseRoles = config('bitwise-permission.base_roles', []);

        if (empty($baseRoles)) {
            $this->warn('  No hay base_roles definidos en config.');
            return;
        }

        $created  = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($baseRoles as $data) {
            $existing = Role::where('name', $data['name'])->first();

            if ($existing) {
                $needsUpdate =
                    $existing->public_name  !== ($data['public_name'] ?? null) ||
                    $existing->description  !== ($data['description'] ?? null) ||
                    $existing->is_base_role !== ($data['is_base_role'] ?? true);

                if ($needsUpdate) {
                    $existing->update([
                        'public_name'  => $data['public_name'],
                        'description'  => $data['description'] ?? null,
                        'is_base_role' => $data['is_base_role'] ?? true,
                    ]);
                    $updated++;
                    $this->line("    <fg=yellow>↻</> Rol '{$data['name']}' actualizado.");
                } else {
                    $skipped++;
                }
            } else {
                Role::create([
                    'name'         => $data['name'],
                    'public_name'  => $data['public_name'],
                    'description'  => $data['description'] ?? null,
                    'is_base_role' => $data['is_base_role'] ?? true,
                ]);
                $created++;
                $this->line("    <fg=green>+</> Rol '{$data['name']}' creado.");
            }
        }

        $this->info("  Roles: <fg=green>{$created} creados</>, <fg=yellow>{$updated} actualizados</>, {$skipped} sin cambios.");
        $this->info('');
    }

    // ─────────────────────────────────────────────────────────
    // Rutas
    // ─────────────────────────────────────────────────────────
    protected function syncRoutes(): void
    {
        $baseRoutes = config('bitwise-permission.base_routes', []);

        if (empty($baseRoutes)) {
            $this->warn('  No hay base_routes definidos en config.');
            return;
        }

        $created  = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($baseRoutes as $data) {
            $existing = AppRoute::where('name', $data['name'])->first();

            if ($existing) {
                $needsUpdate =
                    $existing->type        !== ($data['type'] ?? 'web') ||
                    $existing->patch       !== ($data['patch'] ?? null) ||
                    $existing->description !== ($data['description'] ?? null);

                if ($needsUpdate) {
                    $existing->update([
                        'type'        => $data['type'] ?? 'web',
                        'patch'       => $data['patch'] ?? null,
                        'description' => $data['description'] ?? null,
                    ]);
                    $updated++;
                    $this->line("    <fg=yellow>↻</> Ruta '{$data['name']}' actualizada.");
                } else {
                    $skipped++;
                }
            } else {
                AppRoute::create([
                    'name'        => $data['name'],
                    'type'        => $data['type'] ?? 'web',
                    'patch'       => $data['patch'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);
                $created++;
                $this->line("    <fg=green>+</> Ruta '{$data['name']}' creada.");
            }
        }

        $this->info("  Rutas: <fg=green>{$created} creadas</>, <fg=yellow>{$updated} actualizadas</>, {$skipped} sin cambios.");
        $this->info('');
    }

    // ─────────────────────────────────────────────────────────
    // Menús — soporta children anidados
    // ─────────────────────────────────────────────────────────
    protected function syncMenus(): void
    {
        $baseMenus = config('bitwise-permission.base_menus', []);

        if (empty($baseMenus)) {
            $this->warn('  No hay base_menus definidos en config.');
            return;
        }

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        $this->processMenus($baseMenus, null, $stats);

        $this->info("  Menús: <fg=green>{$stats['created']} creados</>, <fg=yellow>{$stats['updated']} actualizados</>, {$stats['skipped']} sin cambios.");
        $this->info('');
    }

    protected function processMenus(array $menus, ?int $fatherId, array &$stats): void
    {
        foreach ($menus as $menuData) {
            $existing = Menu::where('name', $menuData['name'])->first();

            if ($existing) {
                $needsUpdate =
                    $existing->public_name !== ($menuData['public_name'] ?? null) ||
                    $existing->patch       !== ($menuData['patch'] ?? null) ||
                    $existing->icon        !== ($menuData['icon'] ?? null) ||
                    $existing->order       !== ($menuData['order'] ?? 0) ||
                    $existing->father_id   !== $fatherId;

                if ($needsUpdate) {
                    $existing->update([
                        'public_name' => $menuData['public_name'],
                        'patch'       => $menuData['patch'] ?? null,
                        'icon'        => $menuData['icon'] ?? null,
                        'order'       => $menuData['order'] ?? 0,
                        'father_id'   => $fatherId,
                    ]);
                    $stats['updated']++;
                    $this->line("    <fg=yellow>↻</> Menú '{$menuData['name']}' actualizado.");
                } else {
                    $stats['skipped']++;
                }

                $menu = $existing;
            } else {
                $menu = Menu::create([
                    'name'        => $menuData['name'],
                    'public_name' => $menuData['public_name'],
                    'patch'       => $menuData['patch'] ?? null,
                    'icon'        => $menuData['icon'] ?? null,
                    'order'       => $menuData['order'] ?? 0,
                    'father_id'   => $fatherId,
                ]);
                $stats['created']++;
                $this->line("    <fg=green>+</> Menú '{$menuData['name']}' creado.");
            }

            $this->syncMenuRole($menu, $menuData['roles'] ?? null);

            if (! empty($menuData['children'])) {
                $this->processMenus($menuData['children'], $menu->id, $stats);
            }
        }
    }

    protected function syncMenuRole(Menu $menu, ?array $allowedRoleNames): void
    {
        $prefix    = config('bitwise-permission.table_prefix', 'bwp_');
        $table     = "{$prefix}menu_role";
        $baseRoles = Role::where('is_base_role', true)->get();
        $now       = now();

        foreach ($baseRoles as $role) {
            $disabled = $allowedRoleNames !== null
                ? ! in_array($role->name, $allowedRoleNames, true)
                : false;

            DB::table($table)->updateOrInsert(
                ['menu_id' => $menu->id, 'role_id' => $role->id],
                ['disabled' => $disabled, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
