<?php

namespace HenryHt\BitwisePermission\Observers;

use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Role;

/**
 * MenuObserver
 *
 * Cuando se crea un nuevo ítem de menú:
 *  → Crea un registro en bwp_menu_role por cada rol existente
 *    con disabled = true.
 *
 * El admin luego habilita el ítem para los roles que correspondan
 * desde la UI de roles o de menús.
 */
class MenuObserver
{
    public function created(Menu $menu): void
    {
        $this->createMenuRoleForAllRoles($menu);
    }

    public function deleted(Menu $menu): void
    {
        // Las FK con cascadeOnDelete limpian menu_role automáticamente.
    }

    // ─────────────────────────────────────────────────────────

    protected function createMenuRoleForAllRoles(Menu $menu): void
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            return;
        }

        $now    = now();
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";

        $inserts = $roles->map(fn(Role $role) => [
            'menu_id'    => $menu->id,
            'role_id'    => $role->id,
            'disabled'   => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        \DB::table($table)->insertOrIgnore($inserts);
    }
}