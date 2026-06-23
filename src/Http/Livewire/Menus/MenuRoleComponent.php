<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Menus;

use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Role;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * MenuRoleComponent
 *
 * Vista para gestionar qué ítems de menú ve cada rol.
 * Muestra una tabla: filas = menús, columnas = roles.
 * Cada celda tiene un toggle enabled/disabled.
 *
 * Filtro de roles: base | user | all
 */
class MenuRoleComponent extends Component
{
    public string $roleType = 'base';

    // Estado actual: ['menu_id.role_id' => bool (true = habilitado)]
    public array $state = [];

    // Flash local
    public bool   $saved   = false;
    public string $savedMsg = '';

    public function mount(): void
    {
        $this->loadState();
    }

    public function updatedRoleType(): void
    {
        $this->loadState();
    }

    protected function loadState(): void
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";

        $rows = DB::table($table)
            ->whereIn('role_id', $this->getRoles()->pluck('id'))
            ->get();

        $this->state = [];
        foreach ($rows as $row) {
            $this->state["{$row->menu_id}.{$row->role_id}"] = ! $row->disabled;
        }
    }

    /**
     * Toggle individual — guarda inmediatamente en BD.
     */
    public function toggle(int $menuId, int $roleId): void
    {
        $key      = "{$menuId}.{$roleId}";
        $enabled  = ! ($this->state[$key] ?? false);
        $this->state[$key] = $enabled;

        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";
        $now    = now();

        DB::table($table)->updateOrInsert(
            ['menu_id' => $menuId, 'role_id' => $roleId],
            ['disabled' => ! $enabled, 'updated_at' => $now, 'created_at' => $now]
        );

        $this->flash('Menú actualizado.');
    }

    /**
     * Habilita todos los menús para un rol.
     */
    public function enableAll(int $roleId): void
    {
        $this->setAllForRole($roleId, enabled: true);
        $this->flash('Todos los menús habilitados.');
    }

    /**
     * Deshabilita todos los menús para un rol.
     */
    public function disableAll(int $roleId): void
    {
        $this->setAllForRole($roleId, enabled: false);
        $this->flash('Todos los menús deshabilitados.');
    }

    protected function setAllForRole(int $roleId, bool $enabled): void
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";
        $now    = now();

        $menus = Menu::all();

        foreach ($menus as $menu) {
            $key = "{$menu->id}.{$roleId}";
            $this->state[$key] = $enabled;

            DB::table($table)->updateOrInsert(
                ['menu_id' => $menu->id, 'role_id' => $roleId],
                ['disabled' => ! $enabled, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    protected function flash(string $message): void
    {
        $this->saved    = true;
        $this->savedMsg = $message;
    }

    protected function getRoles()
    {
        $query = Role::orderBy('name');

        return match ($this->roleType) {
            'base' => $query->where('is_base_role', true)->get(),
            'user' => $query->where('is_base_role', false)->get(),
            default => $query->get(),
        };
    }

    public function render()
    {
        $roles = $this->getRoles();
        $menus = Menu::root()
            ->orderBy('order')
            ->with('childrenOrdered')
            ->get();

        return view('bwp::livewire.menus.role', compact('roles', 'menus'));
    }
}