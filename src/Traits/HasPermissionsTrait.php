<?php

namespace HenryHt\BitwisePermission\Traits;

use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Role;
use HenryHt\BitwisePermission\Models\Menu;

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
     * Resuelve el acceso (int bitwise) para una ruta dada.
     * Convierte 'leads.index' → 'leads.*' y consulta la BD.
     * Cachea el resultado para no repetir la query.
     */
    public function resolveAccess(string $routeName): int
    {
        $wildcard = $this->bwpToWildcard($routeName);

        if (array_key_exists($wildcard, $this->bwpAccessCache)) {
            return $this->bwpAccessCache[$wildcard];
        }

        // Verificar si hay permiso wildcard total (super admin)
        $totalWildcard = $this->bwpGetWildcardAccess();
        if ($totalWildcard > 0) {
            return $this->bwpAccessCache[$wildcard] = $totalWildcard;
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
     * Convierte nombre de ruta a wildcard.
     * 'leads.index' → 'leads.*'
     * 'leads.*'     → 'leads.*'
     */
    protected function bwpToWildcard(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $parts[count($parts) - 1] = '*';
        return implode('.', $parts);
    }

    /**
     * Verifica si el rol tiene un permiso wildcard global ('*').
     * Usado para super_admin que tiene acceso a todo.
     */
    protected function bwpGetWildcardAccess(): int
    {
        $roleConfig = config("bitwise-permission.role_permissions.{$this->bwpRole?->name}", []);
        return $roleConfig['*'] ?? 0;
    }
}
