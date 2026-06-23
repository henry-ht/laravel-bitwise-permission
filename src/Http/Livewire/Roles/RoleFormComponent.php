<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Roles;

use HenryHt\BitwisePermission\Models\Role;
use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Models\Access;
use Livewire\Component;

class RoleFormComponent extends Component
{
    public ?Role $role = null;
    public bool  $isEdit = false;

    // Form fields
    public string $name        = '';
    public string $public_name = '';
    public string $description = '';
    public bool   $is_base_role= false;

    // Permisos por ruta: ['route_id' => 'permission_id']
    public array $routePermissions = [];

    // Menús asignados: ['menu_id' => bool]
    public array $menuAccess = [];

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'public_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
            'is_base_role'=> 'boolean',
            'routePermissions.*' => 'nullable|exists:' . (new Permission)->getTable() . ',id',
        ];
    }

    public function mount(?int $roleId = null): void
    {
        if ($roleId) {
            $this->role        = Role::findOrFail($roleId);
            $this->isEdit      = true;
            $this->name        = $this->role->name;
            $this->public_name = $this->role->public_name;
            $this->description = $this->role->description ?? '';
            $this->is_base_role= $this->role->is_base_role;

            // Cargar permisos actuales
            foreach ($this->role->accesses as $access) {
                $this->routePermissions[$access->route_id] = $access->permission_id;
            }

            // Cargar menús actuales
            foreach ($this->role->menus as $menu) {
                $this->menuAccess[$menu->id] = ! $menu->pivot->disabled;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->name,
            'public_name'  => $this->public_name,
            'description'  => $this->description ?: null,
            'is_base_role' => $this->is_base_role,
        ];

        $this->role = $this->isEdit
            ? tap($this->role)->update($data)
            : Role::create($data);

        // Sincronizar accesos
        foreach ($this->routePermissions as $routeId => $permissionId) {
            if ($permissionId) {
                Access::updateOrCreate(
                    ['role_id' => $this->role->id, 'route_id' => $routeId],
                    ['permission_id' => $permissionId]
                );
            } else {
                Access::where(['role_id' => $this->role->id, 'route_id' => $routeId])->delete();
            }
        }

        // Sincronizar menús
        $menuSync = [];
        foreach ($this->menuAccess as $menuId => $enabled) {
            $menuSync[$menuId] = ['disabled' => ! $enabled];
        }
        $this->role->menus()->sync($menuSync);

        session()->flash('bwp_success', $this->isEdit ? 'Rol actualizado.' : 'Rol creado correctamente.');
        $this->redirect(route('bwp.roles.index'));
    }

    public function render()
    {
        $routes      = AppRoute::orderBy('name')->get();
        $permissions = Permission::orderBy('access')->get();
        $menus       = Menu::root()->orderBy('order')->with('childrenOrdered')->get();

        return view('bwp::livewire.roles.form', compact('routes', 'permissions', 'menus'));
    }
}
