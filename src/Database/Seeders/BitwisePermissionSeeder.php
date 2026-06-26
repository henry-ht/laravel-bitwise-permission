<?php

namespace HenryHt\BitwisePermission\Database\Seeders;

use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BitwisePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedBaseRoles();
        $this->seedBaseRoutes();
        $this->seedBaseMenus();
        $this->seedRolePermissions();
    }

    // ─────────────────────────────────────────────────────────
    // 1. Permisos
    // ─────────────────────────────────────────────────────────
    protected function seedPermissions(): void
    {
        $permissions = config('bitwise-permission.base_permissions', []);

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['access' => $permission['access']],
                ['name' => $permission['name']]
            );
        }

        $this->command?->info('  ✓ Permisos: ' . Permission::count());
    }

    // ─────────────────────────────────────────────────────────
    // 2. Roles base
    // ─────────────────────────────────────────────────────────
    protected function seedBaseRoles(): void
    {
        $baseRoles = config('bitwise-permission.base_roles', []);

        foreach ($baseRoles as $data) {
            Role::updateOrCreate(
                ['name' => $data['name']],
                [
                    'public_name'  => $data['public_name'],
                    'description'  => $data['description'] ?? null,
                    'is_base_role' => $data['is_base_role'] ?? true,
                ]
            );
        }

        $this->command?->info('  ✓ Roles base: ' . count($baseRoles));
    }

    // ─────────────────────────────────────────────────────────
    // 3. Rutas base
    // ─────────────────────────────────────────────────────────
    protected function seedBaseRoutes(): void
    {
        $baseRoutes = config('bitwise-permission.base_routes', []);

        foreach ($baseRoutes as $data) {
            AppRoute::updateOrCreate(
                ['name' => $data['name']],
                [
                    'type'        => $data['type']        ?? 'web',
                    'patch'       => $data['patch']       ?? null,
                    'description' => $data['description'] ?? null,
                ]
            );
        }

        $this->command?->info('  ✓ Rutas base: ' . count($baseRoutes));
    }

    // ─────────────────────────────────────────────────────────
    // 4. Menús — soporta children anidados
    // ─────────────────────────────────────────────────────────
    protected function seedBaseMenus(): void
    {
        $baseMenus = config('bitwise-permission.base_menus', []);

        if (empty($baseMenus)) {
            $this->command?->info('  ✓ Menús: sin menús base definidos en config');
            return;
        }

        $count = $this->processMenus($baseMenus, null);

        $this->command?->info("  ✓ Menús: {$count}");
    }

    /**
     * Procesa recursivamente los menús y sus hijos.
     *
     * @param  array    $menus     Lista de ítems
     * @param  int|null $fatherId  ID del padre (null = raíz)
     * @return int                 Total de ítems procesados
     */
    protected function processMenus(array $menus, ?int $fatherId): int
    {
        $count = 0;

        foreach ($menus as $menuData) {
            $menu = Menu::updateOrCreate(
                ['name' => $menuData['name']],
                [
                    'public_name' => $menuData['public_name'],
                    'patch'       => $menuData['patch']  ?? null,
                    'icon'        => $menuData['icon']   ?? null,
                    'order'       => $menuData['order']  ?? 0,
                    'father_id'   => $fatherId,
                ]
            );

            $count++;

            // Relaciones menu_role para este ítem
            $this->seedMenuRoleForItem($menu, $menuData['roles'] ?? null);

            // Procesar hijos recursivamente
            if (! empty($menuData['children'])) {
                $count += $this->processMenus($menuData['children'], $menu->id);
            }
        }

        return $count;
    }

    /**
     * Crea las relaciones menu_role para un ítem.
     * Si 'roles' es null → todos los roles base lo ven habilitado.
     * Si 'roles' es array → solo esos roles lo ven habilitado, los demás disabled.
     */
    protected function seedMenuRoleForItem(Menu $menu, ?array $allowedRoleNames): void
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

    // ─────────────────────────────────────────────────────────
    // 5. Accesos por rol (desde config)
    // ─────────────────────────────────────────────────────────
    protected function seedRolePermissions(): void
    {
        $rolePermissions = config('bitwise-permission.role_permissions', []);
        $count           = 0;

        foreach ($rolePermissions as $roleName => $routeAccesses) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                $this->command?->warn("  ⚠ Rol '{$roleName}' no encontrado.");
                continue;
            }

            foreach ($routeAccesses as $routeName => $accessValue) {
                // '*' es solo para super_admin en config, se resuelve en el trait
                if ($routeName === '*') {
                    continue;
                }

                $route = AppRoute::where('name', $routeName)->first();

                if (! $route) {
                    $this->command?->warn("  ⚠ Ruta '{$routeName}' no encontrada.");
                    continue;
                }

                $permission = Permission::where('access', $accessValue)->first();

                if (! $permission) {
                    $this->command?->warn("  ⚠ Permiso con access={$accessValue} no encontrado.");
                    continue;
                }

                Access::updateOrCreate(
                    ['role_id' => $role->id, 'route_id' => $route->id],
                    ['permission_id' => $permission->id]
                );

                $count++;
            }
        }

        $this->command?->info("  ✓ Accesos: {$count}");
    }
}