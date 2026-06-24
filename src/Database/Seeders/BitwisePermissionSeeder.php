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
        $this->seedMenuRoleRelations();
    }

    // ─────────────────────────────────────────────────────────
    // 1. Permisos
    // ─────────────────────────────────────────────────────────
    protected function seedPermissions(): void
    {
        $bits      = config('bitwise-permission.bits', []);
        $bitValues = array_values($bits);
        $viewBit   = $bits['view'] ?? 1;
        $count     = count($bitValues);
        $total     = (1 << $count) - 1;

        Permission::updateOrCreate(['access' => 0], ['name' => 'no access']);

        for ($mask = 1; $mask <= $total; $mask++) {
            $value = 0;
            $names = [];

            foreach ($bitValues as $i => $bit) {
                if ($mask & (1 << $i)) {
                    $value |= $bit;
                    $names[] = array_keys($bits)[$i];
                }
            }

            if (! ($value & $viewBit)) {
                continue;
            }

            Permission::updateOrCreate(
                ['access' => $value],
                ['name'   => implode(' + ', $names)]
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
                    'patch'       => $data['patch']        ?? null,
                    'description' => $data['description']  ?? null,
                ]
            );
        }

        $this->command?->info('  ✓ Rutas base: ' . count($baseRoutes));
    }

    // ─────────────────────────────────────────────────────────
    // 4. Menús por defecto
    // ─────────────────────────────────────────────────────────
    protected function seedBaseMenus(): void
    {
        $baseMenus = config('bitwise-permission.base_menus', []);

        if (empty($baseMenus)) {
            $this->command?->info('  ✓ Menús: sin menús base definidos en config');
            return;
        }

        foreach ($baseMenus as $menuData) {
            $fatherId = null;

            if (isset($menuData['father_name'])) {
                $parent   = Menu::where('name', $menuData['father_name'])->first();
                $fatherId = $parent?->id;
            }

            Menu::updateOrCreate(
                ['name' => $menuData['name']],
                [
                    'public_name' => $menuData['public_name'],
                    'patch'       => $menuData['patch']       ?? null,
                    'icon'        => $menuData['icon']        ?? null,
                    'order'       => $menuData['order']       ?? 0,
                    'father_id'   => $fatherId,
                ]
            );
        }

        $this->command?->info('  ✓ Menús: ' . count($baseMenus));
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
                // if ($routeName === '*') {
                //     continue;
                // }

                $route = AppRoute::where('name', $routeName)->first();

                if (! $route) {
                    $this->command?->warn("  ⚠ Ruta '{$routeName}' no encontrada.");
                    continue;
                }

                $bitNames   = $accessValue > 0 ? BitwiseHelper::decode($accessValue) : [];
                $permission = Permission::where('access', $accessValue)->firstOrFail();

                Access::updateOrCreate(
                    ['role_id' => $role->id, 'route_id' => $route->id],
                    ['permission_id' => $permission->id]
                );

                $count++;
            }
        }

        $this->command?->info("  ✓ Accesos: {$count}");
    }

    // ─────────────────────────────────────────────────────────
    // 6. Relaciones menú-rol por defecto
    // Todos los roles base ven todos los menús habilitados.
    // Los menús con 'roles' definidos respetan esa lista.
    // ─────────────────────────────────────────────────────────
    protected function seedMenuRoleRelations(): void
    {
        $prefix    = config('bitwise-permission.table_prefix', 'bwp_');
        $table     = "{$prefix}menu_role";
        $baseMenus = config('bitwise-permission.base_menus', []);
        $baseRoles = Role::where('is_base_role', true)->get();
        $now       = now();
        $count     = 0;

        foreach ($baseMenus as $menuData) {
            $menu = Menu::where('name', $menuData['name'])->first();

            if (! $menu) {
                continue;
            }

            // Roles que pueden ver este menú (desde config o todos los base)
            $allowedRoleNames = $menuData['roles'] ?? null;

            foreach ($baseRoles as $role) {
                $disabled = false;

                // Si el menú define roles específicos, los demás quedan disabled
                if ($allowedRoleNames !== null) {
                    $disabled = ! in_array($role->name, $allowedRoleNames, true);
                }

                DB::table($table)->updateOrInsert(
                    ['menu_id' => $menu->id, 'role_id' => $role->id],
                    ['disabled' => $disabled, 'updated_at' => $now, 'created_at' => $now]
                );

                $count++;
            }
        }

        $this->command?->info("  ✓ Relaciones menú-rol: {$count}");
    }
}
