<?php

namespace HenryHt\BitwisePermission\Observers;

use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Models\Role;

/**
 * AppRouteObserver
 *
 * Cuando se crea una nueva ruta:
 *  → Crea un registro en bwp_accesses por cada rol existente
 *    con permiso "no access" (0).
 *
 * Así el admin ve la nueva ruta en la UI de accesos
 * y puede activar los permisos que correspondan.
 */
class AppRouteObserver
{
    public function created(AppRoute $route): void
    {
        $this->createAccessesForAllRoles($route);
    }

    public function deleted(AppRoute $route): void
    {
        // Las FK con cascadeOnDelete limpian los accesses automáticamente.
    }

    // ─────────────────────────────────────────────────────────

    protected function createAccessesForAllRoles(AppRoute $route): void
    {
        $noAccess = Permission::where('access', 0)->first();

        if (! $noAccess) {
            return;
        }

        $roles = Role::all();

        if ($roles->isEmpty()) {
            return;
        }

        $now    = now();
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}accesses";

        $inserts = $roles->map(fn(Role $role) => [
            'role_id'       => $role->id,
            'route_id'      => $route->id,
            'permission_id' => $noAccess->id,
            'created_at'    => $now,
            'updated_at'    => $now,
        ])->toArray();

        \DB::table($table)->insertOrIgnore($inserts);
    }
}