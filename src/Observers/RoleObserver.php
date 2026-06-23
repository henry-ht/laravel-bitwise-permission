<?php

namespace HenryHt\BitwisePermission\Observers;

use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Models\Role;

/**
 * RoleObserver
 *
 * Cuando se crea un nuevo rol:
 *  1. Crea un registro en bwp_accesses por cada ruta existente
 *     con permiso "no access" (0) — el admin luego activa los que necesite.
 *  2. Crea un registro en bwp_menu_role por cada menú existente
 *     con disabled = true — el admin luego habilita los que necesite.
 */
class RoleObserver
{
    public function created(Role $role): void
    {
        $this->createAccessesForAllRoutes($role);
        $this->createMenuRelationsForAllMenus($role);
    }

    public function deleted(Role $role): void
    {
        // Las FK con cascadeOnDelete se encargan de limpiar
        // accesses y menu_role automáticamente.
    }

    // ─────────────────────────────────────────────────────────

    protected function createAccessesForAllRoutes(Role $role): void
    {
        $noAccess = Permission::where('access', 0)->first();

        if (! $noAccess) {
            return;
        }

        $routes = AppRoute::all();

        if ($routes->isEmpty()) {
            return;
        }

        $now    = now();
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}accesses";

        $inserts = $routes->map(fn(AppRoute $route) => [
            'role_id'       => $role->id,
            'route_id'      => $route->id,
            'permission_id' => $noAccess->id,
            'created_at'    => $now,
            'updated_at'    => $now,
        ])->toArray();

        // insertOrIgnore evita duplicados si ya existía alguno
        \DB::table($table)->insertOrIgnore($inserts);
    }

    protected function createMenuRelationsForAllMenus(Role $role): void
    {
        $menus = Menu::all();

        if ($menus->isEmpty()) {
            return;
        }

        $now    = now();
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";

        $inserts = $menus->map(fn(Menu $menu) => [
            'menu_id'    => $menu->id,
            'role_id'    => $role->id,
            'disabled'   => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        \DB::table($table)->insertOrIgnore($inserts);
    }
}