<?php

namespace HenryHt\BitwisePermission\Traits;

use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Role;
use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Permission;

/**
 * Trait HasPermissionsTrait
 *
 * Incluir en el modelo que gestiona permisos (ej: User).
 *
 * Uso:
 *   use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;
 *
 *   class User extends Authenticatable {
 *       use HasPermissionsTrait;
 *   }
 *
 * El modelo debe tener la columna: role_id (FK a bwp_roles)
 */
trait HasPermissionsTrait
{
    /**
     * Cache de acceso por ruta wildcard para el request actual.
     * Evita queries repetidas: clave = wildcard, valor = int.
     */
    protected array $bwpAccessCache = [];

    /**
     * Acceso activo seteado por el middleware para el request actual.
     */
    protected int $bwpCurrentAccess = 0;

    // ─────────────────────────────────────────────────────────
    // Relación con Role
    // ─────────────────────────────────────────────────────────

    public function bwpRole()
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        return $this->belongsTo(Role::class, 'role_id');
    }

    // ─────────────────────────────────────────────────────────
    // Resolución de acceso
    // ─────────────────────────────────────────────────────────


    /**
     * Setea el acceso activo del request (llamado desde middleware).
     */
    public function setAccess(int $access): void
    {
        $this->bwpCurrentAccess = $access;
    }

    /**
     * Retorna el acceso activo del request.
     */
    public function getAccess(): int
    {
        return $this->bwpCurrentAccess;
    }

    /**
     * Limpia el cache de acceso (útil en tests).
     */
    public function clearAccessCache(): void
    {
        $this->bwpAccessCache = [];
    }

    // ─────────────────────────────────────────────────────────
    // Verificadores — todos requieren view (bit 1) activo
    // ─────────────────────────────────────────────────────────

    public function canView(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view');
    }

    public function canViewAny(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'view_any');
    }

    public function canCreate(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'create');
    }

    public function canUpdate(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'update');
    }

    public function canDelete(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'delete');
    }

    public function canRestore(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'restore');
    }

    public function canForceDelete(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'force_delete');
    }

    public function canChangeStatus(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'change_status');
    }

    public function canAssign(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'assign');
    }

    public function canSupport(?string $routeName = null): bool
    {
        return $this->bwpCheck($routeName, 'view')
            && $this->bwpCheck($routeName, 'support');
    }

    /**
     * Verifica un bit extendido personalizado por su nombre de config.
     * Ejemplo: $user->canCustom('export', 'products.*')
     */
    public function canCustom(string $bitName, ?string $routeName = null): bool
    {
        $bit = config("bitwise-permission.bits.{$bitName}");

        if (! $bit) {
            return false;
        }

        return $this->bwpCheck($routeName, 'view')
            && $this->bwpHasBit($this->bwpResolveValue($routeName), $bit);
    }

    /**
     * Verifica si tiene acceso total (todos los bits activos).
     */
    public function hasTotalAccess(?string $routeName = null): bool
    {
        $bits  = config('bitwise-permission.bits', []);
        $total = array_sum($bits);
        $value = $this->bwpResolveValue($routeName);

        return ($value & $total) === $total;
    }

    // ─────────────────────────────────────────────────────────
    // Menú: ítems visibles para el rol del usuario
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna los ítems raíz del menú visibles para el rol del usuario,
     * con sus hijos ordenados cargados recursivamente.
     */
    public function getMenu(): \Illuminate\Database\Eloquent\Collection
    {
        return Menu::root()
                ->forRole($this->role_id)
                ->orderBy('order')
                ->with([
                    'childrenOrdered' => function ($query) {
                        $query->forRole($this->role_id);
                    }
                ])
                ->get();
    }

    // ─────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────

    protected function bwpCheck(?string $routeName, string $bitName): bool
    {
        $bit   = config("bitwise-permission.bits.{$bitName}", 0);
        $value = $this->bwpResolveValue($routeName);
        return $this->bwpHasBit($value, $bit);
    }

    protected function bwpResolveValue(?string $routeName): int
    {
        return $routeName
            ? $this->resolveAccess($routeName)
            : $this->bwpCurrentAccess;
    }

    protected function bwpHasBit(int $access, int $bit): bool
    {
        return $bit > 0 && ($access & $bit) === $bit;
    }

    /**
     * Convierte una ruta resource de Laravel a wildcard.
     *
     * Ejemplos:
     *  - leads.index   -> leads.*
     *  - leads.create  -> leads.*
     *  - leads.store   -> leads.*
     *  - leads.show    -> leads.*
     *  - leads.edit    -> leads.*
     *  - leads.update  -> leads.*
     *  - leads.destroy -> leads.*
     *
     * Las demás rutas permanecen igual:
     *  - web.dashboard      -> web.dashboard
     *  - web.orders         -> web.orders
     *  - web.invoice.ticket -> web.invoice.ticket
     */
    protected function bwpToWildcard(string $routeName): string
    {
        $resourceActions = [
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ];

        $parts = explode('.', $routeName);

        if (in_array(end($parts), $resourceActions, true)) {
            $parts[array_key_last($parts)] = '*';
        }

        return implode('.', $parts);
    }
    
    // Agrega estos métodos al trait HasPermissionsTrait
    // dentro de la sección de helpers públicos

    // ─────────────────────────────────────────────────────────
    // Gestión dinámica de permisos
    // ─────────────────────────────────────────────────────────

    /**
     * Cambia el permiso del rol del usuario para una ruta específica.
     *
     * Uso:
     *   // Por nombre de permiso definido en config base_permissions
     *   $user->setPermission('leads.*', 'modify access');
     *
     *   // Por valor numérico directo
     *   $user->setPermission('leads.*', 13);
     *
     * @param  string     $routeName       Wildcard o nombre de ruta: 'leads.*', 'leads.index'
     * @param  string|int $permissionValue Nombre del permiso (string) o valor bitwise (int)
     * @return bool                        true si se guardó, false si la ruta o permiso no existe
     */
    public function setPermission(string $routeName, string|int $permissionValue): bool
    {
        $wildcard = $this->bwpToWildcard($routeName);

        // Resolver la ruta
        $route = AppRoute::where('name', $wildcard)->first();

        if (! $route) {
            return false;
        }

        // Resolver el permiso — por nombre o por valor numérico
        $permission = is_string($permissionValue)
            ? Permission::where('name', $permissionValue)->first()
            : Permission::where('access', $permissionValue)->first();

        if (! $permission) {
            return false;
        }

        // Actualizar o crear el acceso para el rol del usuario
        Access::updateOrCreate(
            [
                'role_id'  => $this->role_id,
                'route_id' => $route->id,
            ],
            [
                'permission_id' => $permission->id,
            ]
        );

        // Limpiar cache para que el nuevo permiso aplique inmediatamente
        unset($this->bwpAccessCache[$wildcard]);

        return true;
    }

    /**
     * Cambia permisos en múltiples rutas de una sola vez.
     *
     * Uso:
     *   $user->setPermissions([
     *       'leads.*'    => 'modify access',
     *       'deals.*'    => 'read access',
     *       'contacts.*' => 'no access',
     *   ]);
     *
     * @param  array<string, string|int> $permissions  ['ruta' => 'nombre permiso' | int]
     * @return array<string, bool>                     ['ruta' => true|false] resultado por ruta
     */
    public function setPermissions(array $permissions): array
    {
        $results = [];

        foreach ($permissions as $routeName => $permissionValue) {
            $results[$routeName] = $this->setPermission($routeName, $permissionValue);
        }

        return $results;
    }

    /**
     * Retorna el permiso actual del usuario para una ruta.
     * Útil para pre-cargar formularios de gestión de permisos.
     *
     * @param  string   $routeName  Wildcard o nombre de ruta
     * @return ?Permission          null si no tiene acceso configurado
     */
    public function getPermissionFor(string $routeName): ?Permission
    {
        $wildcard = $this->bwpToWildcard($routeName);
        $route    = AppRoute::where('name', $wildcard)->first();

        if (! $route) {
            return null;
        }

        $access = Access::where('role_id',  $this->role_id)
                        ->where('route_id', $route->id)
                        ->with('permission')
                        ->first();

        return $access?->permission;
    }

    /**
     * Retorna todos los permisos del usuario agrupados por ruta.
     * Útil para renderizar una vista completa de permisos.
     *
     * @return \Illuminate\Support\Collection  [route_name => Permission]
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return Access::where('role_id', $this->role_id)
            ->with(['route', 'permission'])
            ->get()
            ->mapWithKeys(fn($access) => [
                $access->route->name => $access->permission
            ]);
    }
    
    // Agrega estos métodos al trait HasPermissionsTrait
    // en la sección de "Gestión dinámica de permisos"

    // ─────────────────────────────────────────────────────────
    // Gestión dinámica de menú
    // ─────────────────────────────────────────────────────────

    /**
     * Habilita o deshabilita un ítem de menú para el rol del usuario.
     * Si el ítem tiene padre, actualiza el padre automáticamente:
     *  - Si se habilita un hijo → habilita el padre
     *  - Si se deshabilita un hijo → deshabilita el padre solo si
     *    no quedan otros hijos habilitados
     *
     * Uso:
     *   $user->setMenuAccess('leads', true);   // habilitar
     *   $user->setMenuAccess('leads', false);  // deshabilitar
     *   $user->setMenuAccess(3, true);         // por ID
     *
     * @param  string|int $menuIdentifier  Nombre (slug) o ID del ítem de menú
     * @param  bool       $enabled         true = habilitar, false = deshabilitar
     * @return bool                        false si el menú no existe
     */
    public function setMenuAccess(string|int $menuIdentifier, bool $enabled): bool
    {
        $menu = is_int($menuIdentifier)
            ? Menu::find($menuIdentifier)
            : Menu::where('name', $menuIdentifier)->first();

        if (! $menu) {
            return false;
        }

        $this->updateMenuRole($menu->id, $enabled);

        // Propagar cambio al padre si existe
        if ($menu->father_id) {
            $this->syncParentMenuAccess($menu->father_id);
        }

        return true;
    }

    /**
     * Habilita o deshabilita múltiples ítems de menú de una vez.
     *
     * Uso:
     *   $user->setMenuAccesses([
     *       'leads'        => true,
     *       'leads-create' => true,
     *       'deals'        => false,
     *   ]);
     *
     * @param  array<string|int, bool> $items  [nombre|id => bool]
     * @return array<string, bool>             resultado por ítem
     */
    public function setMenuAccesses(array $items): array
    {
        $results = [];

        foreach ($items as $identifier => $enabled) {
            $results[$identifier] = $this->setMenuAccess($identifier, $enabled);
        }

        return $results;
    }

    /**
     * Verifica si un ítem de menú está habilitado para el rol del usuario.
     *
     * @param  string|int $menuIdentifier  Nombre o ID del ítem
     * @return bool
     */
    public function hasMenuAccess(string|int $menuIdentifier): bool
    {
        $menu = is_int($menuIdentifier)
            ? Menu::find($menuIdentifier)
            : Menu::where('name', $menuIdentifier)->first();

        if (! $menu) {
            return false;
        }

        $prefix     = config('bitwise-permission.table_prefix', 'bwp_');
        $pivotTable = $prefix . 'menu_role';

        $row = \Illuminate\Support\Facades\DB::table($pivotTable)
            ->where('menu_id', $menu->id)
            ->where('role_id', $this->role_id)
            ->first();

        // Si no existe registro → por defecto no tiene acceso
        return $row ? ! $row->disabled : false;
    }

    // ─────────────────────────────────────────────────────────
    // Helpers internos de menú
    // ─────────────────────────────────────────────────────────

    /**
     * Inserta o actualiza el registro en menu_role.
     */
    protected function updateMenuRole(int $menuId, bool $enabled): void
    {
        $prefix     = config('bitwise-permission.table_prefix', 'bwp_');
        $pivotTable = $prefix . 'menu_role';
        $now        = now();

        \Illuminate\Support\Facades\DB::table($pivotTable)->updateOrInsert(
            ['menu_id' => $menuId, 'role_id' => $this->role_id],
            ['disabled' => ! $enabled, 'updated_at' => $now, 'created_at' => $now]
        );
    }

    /**
     * Sincroniza el estado del menú padre según sus hijos:
     * - Si al menos un hijo está habilitado → habilitar padre
     * - Si ningún hijo está habilitado → deshabilitar padre
     * - Propaga recursivamente hacia arriba si el padre también tiene padre
     */
    protected function syncParentMenuAccess(int $parentId): void
    {
        $prefix     = config('bitwise-permission.table_prefix', 'bwp_');
        $menuTable  = $prefix . 'menus';
        $pivotTable = $prefix . 'menu_role';

        // Obtener todos los hijos directos del padre
        $childIds = \Illuminate\Support\Facades\DB::table($menuTable)
            ->where('father_id', $parentId)
            ->pluck('id')
            ->toArray();

        if (empty($childIds)) {
            return;
        }

        // Verificar si algún hijo está habilitado para este rol
        $hasEnabledChild = \Illuminate\Support\Facades\DB::table($pivotTable)
            ->whereIn('menu_id', $childIds)
            ->where('role_id', $this->role_id)
            ->where('disabled', false)
            ->exists();

        // Actualizar el padre según el estado de sus hijos
        $this->updateMenuRole($parentId, $hasEnabledChild);

        // Propagar recursivamente si el padre también tiene un padre
        $grandParentId = \Illuminate\Support\Facades\DB::table($menuTable)
            ->where('id', $parentId)
            ->value('father_id');

        if ($grandParentId) {
            $this->syncParentMenuAccess($grandParentId);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Resolución de acceso
    // ─────────────────────────────────────────────────────────
    
    /**
     * Verifica si el usuario es super admin según la config.
     * Si es super admin, retorna acceso total sin consultar la BD.
     */
    public function isSuperAdmin(): bool
    {
        $superAdminRole = config('bitwise-permission.super_admin_role');
    
        if (! $superAdminRole) {
            return false;
        }
    
        return $this->bwpRole?->name === $superAdminRole;
    }

    /**
     * Resuelve el acceso (int bitwise) para una ruta dada.
     * Convierte 'leads.index' → 'leads.*' y consulta la BD.
     * Cachea el resultado para no repetir la query.
     * Todo se verifica desde la base de datos — incluyendo super_admin.
     */
    public function resolveAccess(string $routeName): int
    {
        $wildcard = $this->bwpToWildcard($routeName);
    
        if (array_key_exists($wildcard, $this->bwpAccessCache)) {
            return $this->bwpAccessCache[$wildcard];
        }
    
        $route = AppRoute::where('name', $wildcard)->first();
    
        if (! $route) {
            return $this->bwpAccessCache[$wildcard] = 0;
        }
    
        $access = Access::where([
            'role_id'  => $this->role_id,
            'route_id' => $route->id,
        ])->with('permission')->first();
    
        $value = $access?->permission?->access ?? 0;
    
        return $this->bwpAccessCache[$wildcard] = $value;
    }
    
    /**
     * hasTotalAccess ahora también considera super admin.
     */
    public function hasTotalAccess(?string $routeName = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
    
        $bits  = config('bitwise-permission.bits', []);
        $total = array_sum(array_filter($bits, fn($v) => $v > 0));
        $value = $this->bwpResolveValue($routeName);
    
        return ($value & $total) === $total;
    }
}
