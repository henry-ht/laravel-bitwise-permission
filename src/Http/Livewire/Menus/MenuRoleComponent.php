<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Menus;

use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Role;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MenuRoleComponent extends Component
{
    // Rol seleccionado
    public int    $selectedRoleId = 0;
    public string $roleType       = 'all'; // filtro del select de roles

    // Estado: ['menu_id' => bool (true = habilitado)]
    public array $state = [];

    // Flash
    public bool   $saved    = false;
    public string $savedMsg = '';

    public function mount(): void
    {
        // Pre-seleccionar el primer rol base disponible
        $first = Role::where('is_base_role', true)->orderBy('name')->first();
        if ($first) {
            $this->selectedRoleId = $first->id;
            $this->loadState();
        }
    }

    public function updatedSelectedRoleId(): void
    {
        $this->saved = false;
        $this->loadState();
    }

    public function updatedRoleType(): void
    {
        // Al cambiar filtro resetear selección
        $this->selectedRoleId = 0;
        $this->state          = [];
        $this->saved          = false;
    }

    protected function loadState(): void
    {
        if (! $this->selectedRoleId) {
            $this->state = [];
            return;
        }

        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";

        $rows = DB::table($table)
            ->where('role_id', $this->selectedRoleId)
            ->get();

        $this->state = [];
        foreach ($rows as $row) {
            $this->state[$row->menu_id] = ! $row->disabled;
        }
    }

    /**
     * Toggle individual de un menú para el rol seleccionado.
     */
    public function toggle(int $menuId): void
    {
        if (! $this->selectedRoleId) {
            return;
        }

        $enabled              = ! ($this->state[$menuId] ?? false);
        $this->state[$menuId] = $enabled;

        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $now    = now();

        DB::table("{$prefix}menu_role")->updateOrInsert(
            ['menu_id' => $menuId, 'role_id' => $this->selectedRoleId],
            ['disabled' => ! $enabled, 'updated_at' => $now, 'created_at' => $now]
        );

        $this->flash('Menú actualizado.');
    }

    /**
     * Habilita todos los menús para el rol seleccionado.
     */
    public function enableAll(): void
    {
        $this->setAll(enabled: true);
        $this->flash('Todos los menús habilitados.');
    }

    /**
     * Deshabilita todos los menús para el rol seleccionado.
     */
    public function disableAll(): void
    {
        $this->setAll(enabled: false);
        $this->flash('Todos los menús deshabilitados.');
    }

    protected function setAll(bool $enabled): void
    {
        if (! $this->selectedRoleId) {
            return;
        }

        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $now    = now();

        $menus = Menu::all();

        foreach ($menus as $menu) {
            $this->state[$menu->id] = $enabled;

            DB::table("{$prefix}menu_role")->updateOrInsert(
                ['menu_id' => $menu->id, 'role_id' => $this->selectedRoleId],
                ['disabled' => ! $enabled, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    protected function flash(string $message): void
    {
        $this->saved    = true;
        $this->savedMsg = $message;
    }

    public function render()
    {
        $roles = Role::when(
                $this->roleType === 'base', fn($q) => $q->where('is_base_role', true)
            )
            ->when(
                $this->roleType === 'user', fn($q) => $q->where('is_base_role', false)
            )
            ->orderBy('name')
            ->get();

        $menus = $this->selectedRoleId
            ? Menu::root()->orderBy('order')->with('childrenOrdered')->get()
            : collect();

        $selectedRole = $this->selectedRoleId
            ? Role::find($this->selectedRoleId)
            : null;

        return view('bwp::livewire.menus.role', compact('roles', 'menus', 'selectedRole'));
    }
}