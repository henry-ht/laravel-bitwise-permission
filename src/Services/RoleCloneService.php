<?php

namespace HenryHt\BitwisePermission\Services;

use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RoleCloneService
 *
 * Clona un rol base y lo asigna a un usuario específico.
 *
 * Cuando se crea un usuario con un rol base (ej: 'user'),
 * este servicio:
 *  1. Crea un nuevo rol llamado 'user_[random8]'
 *  2. Copia todos los accesses del rol base al nuevo rol
 *  3. Copia todas las relaciones menu_role del rol base
 *  4. Asigna el nuevo rol al usuario
 *
 * Uso:
 *   $service = app(RoleCloneService::class);
 *   $newRole = $service->cloneForUser($user, $baseRole);
 *
 * O inyectado en un controlador / acción:
 *   public function store(Request $request, RoleCloneService $roleClone)
 *   {
 *       $user     = User::create([...]);
 *       $baseRole = Role::where('name', $request->role)->firstOrFail();
 *       $roleClone->cloneForUser($user, $baseRole);
 *   }
 */
class RoleCloneService
{
    /**
     * Clona el rol base y lo asigna al usuario.
     *
     * @param  Model  $user      El usuario que recibirá el rol clonado
     * @param  Role   $baseRole  El rol base a clonar (is_base_role = true)
     * @return Role              El nuevo rol clonado
     */
    public function cloneForUser(Model $user, Role $baseRole): Role
    {
        $newRole = $this->createClonedRole($baseRole);

        $this->cloneAccesses($baseRole, $newRole);
        $this->cloneMenuRelations($baseRole, $newRole);

        // Asignar el nuevo rol al usuario
        $user->role_id = $newRole->id;
        $user->save();

        return $newRole;
    }

    /**
     * Clona el rol base sin asignarlo a nadie.
     * Útil para crear variantes manuales de un rol.
     *
     * @param  Role        $baseRole  El rol a clonar
     * @param  string|null $suffix    Sufijo personalizado (si null → random 8 chars)
     * @return Role
     */
    public function cloneRole(Role $baseRole, ?string $suffix = null): Role
    {
        $newRole = $this->createClonedRole($baseRole, $suffix);
        $this->cloneAccesses($baseRole, $newRole);
        $this->cloneMenuRelations($baseRole, $newRole);

        return $newRole;
    }

    // ─────────────────────────────────────────────────────────
    // Internos
    // ─────────────────────────────────────────────────────────

    protected function createClonedRole(Role $baseRole, ?string $suffix = null): Role
    {
        $suffix = $suffix ?? Str::lower(Str::random(8));

        return Role::create([
            'name'         => "{$baseRole->name}_{$suffix}",
            'public_name'  => $baseRole->public_name,
            'description'  => $baseRole->description,
            'is_base_role' => false,  // los roles clonados NUNCA son base
            'base_role_id' => $baseRole->id, // ← registra de qué rol base proviene
        ]);

        // Nota: el RoleObserver se dispara aquí y crea las relaciones
        // accesses (no access) y menu_role (disabled) automáticamente.
        // Luego las sobreescribimos con los datos del rol base.
    }

    protected function cloneAccesses(Role $baseRole, Role $newRole): void
    {
        $prefix       = config('bitwise-permission.table_prefix', 'bwp_');
        $table        = "{$prefix}accesses";
        $baseAccesses = Access::where('role_id', $baseRole->id)->get();

        if ($baseAccesses->isEmpty()) {
            return;
        }

        $now = now();

        // Sobreescribir los accesses que el Observer creó con "no access"
        foreach ($baseAccesses as $access) {
            DB::table($table)->updateOrInsert(
                ['role_id' => $newRole->id, 'route_id' => $access->route_id],
                [
                    'permission_id' => $access->permission_id,
                    'updated_at'    => $now,
                    'created_at'    => $now,
                ]
            );
        }
    }

    protected function cloneMenuRelations(Role $baseRole, Role $newRole): void
    {
        $prefix   = config('bitwise-permission.table_prefix', 'bwp_');
        $table    = "{$prefix}menu_role";
        $now      = now();

        $menuRoles = DB::table($table)
            ->where('role_id', $baseRole->id)
            ->get();

        if ($menuRoles->isEmpty()) {
            return;
        }

        // Sobreescribir las relaciones que el Observer creó con disabled = true
        foreach ($menuRoles as $mr) {
            DB::table($table)->updateOrInsert(
                ['menu_id' => $mr->menu_id, 'role_id' => $newRole->id],
                [
                    'disabled'   => $mr->disabled,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
    
    /**
     * Elimina por completo el rol asociado a un usuario.
     *
     * Elimina:
     *  - Todos los accesses (bwp_accesses) del rol
     *  - Todas las relaciones de menú (bwp_menu_role) del rol
     *  - El rol mismo (bwp_roles)
     *
     * Por seguridad, NUNCA elimina un rol base (is_base_role = true).
     * Si el usuario tiene un rol base asignado, retorna false sin hacer nada.
     *
     * Uso:
     *   app(RoleCloneService::class)->deleteRoleForUser($user);
     *
     * @param  Model $user  Usuario cuyo rol se va a eliminar
     * @return bool         true si se eliminó, false si no se pudo (rol base o sin rol)
     */
    public function deleteRoleForUser(Model $user): bool
    {
        if (! $user->role_id) {
            return false;
        }

        $role = Role::find($user->role_id);

        if (! $role) {
            return false;
        }

        // Protección absoluta — jamás eliminar un rol base
        if ($role->is_base_role) {
            return false;
        }

        $prefix = config('bitwise-permission.table_prefix', 'bwp_');

        // 1. Desvincular al usuario del rol antes de borrar (evita FK huérfana)
        $user->role_id = null;
        $user->save();

        // 2. Eliminar accesses del rol
        DB::table("{$prefix}accesses")
            ->where('role_id', $role->id)
            ->delete();

        // 3. Eliminar relaciones de menú del rol
        DB::table("{$prefix}menu_role")
            ->where('role_id', $role->id)
            ->delete();

        // 4. Eliminar el rol
        $role->delete();

        return true;
    }

    /**
     * Elimina un rol por su ID directamente (sin pasar por un usuario).
     * Útil para limpieza administrativa.
     * Misma protección — jamás elimina roles base.
     *
     * @param  int $roleId
     * @return bool
     */
    public function deleteRoleById(int $roleId): bool
    {
        $role = Role::find($roleId);

        if (! $role || $role->is_base_role) {
            return false;
        }

        $prefix = config('bitwise-permission.table_prefix', 'bwp_');

        // Desvincular usuarios que tengan este rol asignado
        config('bitwise-permission.user_model', \App\Models\User::class)::where('role_id', $roleId)
            ->update(['role_id' => null]);

        DB::table("{$prefix}accesses")->where('role_id', $roleId)->delete();
        DB::table("{$prefix}menu_role")->where('role_id', $roleId)->delete();

        $role->delete();

        return true;
    }
}