<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Accesses;

use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Models\Role;
use Livewire\Component;

class AccessFormComponent extends Component
{
    public ?Access $access  = null;
    public bool    $isEdit  = false;

    public int    $role_id       = 0;
    public int    $route_id      = 0;
    public int    $permission_id = 0;

    // Bits seleccionados visualmente
    public array $selectedBits = [];

    // Filtro de tipo de rol: 'base' | 'user' | 'all'
    public string $roleType = 'all';

    protected function rules(): array
    {
        $accessTable = (new Access)->getTable();
        $uniqueRule  = "unique:{$accessTable},route_id,NULL,id,role_id,{$this->role_id}";

        if ($this->isEdit) {
            $uniqueRule = "unique:{$accessTable},route_id,{$this->access->id},id,role_id,{$this->role_id}";
        }

        return [
            'role_id'       => 'required|integer|min:1',
            'route_id'      => "required|integer|min:1|{$uniqueRule}",
            'permission_id' => 'required|integer|min:1',
        ];
    }

    public function mount(?int $accessId = null): void
    {
        if ($accessId) {
            $this->access        = Access::with('permission')->findOrFail($accessId);
            $this->isEdit        = true;
            $this->role_id       = $this->access->role_id;
            $this->route_id      = $this->access->route_id;
            $this->permission_id = $this->access->permission_id;

            // Cargar bits activos del permiso existente
            $this->selectedBits = BitwiseHelper::decode($this->access->permission->access);
        }
    }

    /**
     * Al cambiar el rol, si estamos editando cargamos los bits actuales
     * que tiene ese rol sobre la ruta seleccionada.
     */
    public function updatedRoleId(): void
    {
        $this->loadCurrentBitsForRoleRoute();
    }

    public function updatedRouteId(): void
    {
        $this->loadCurrentBitsForRoleRoute();
    }

    /**
     * Carga los bits que ya tiene el rol sobre la ruta — para mostrarlos
     * pre-seleccionados en el formulario de edición.
     */
    protected function loadCurrentBitsForRoleRoute(): void
    {
        if (! $this->role_id || ! $this->route_id) {
            return;
        }

        $existing = Access::where('role_id', $this->role_id)
            ->where('route_id', $this->route_id)
            ->with('permission')
            ->first();

        if ($existing) {
            $this->selectedBits  = BitwiseHelper::decode($existing->permission->access);
            $this->permission_id = $existing->permission_id;
            $this->access        = $existing;
            $this->isEdit        = true;
        } else {
            $this->selectedBits  = [];
            $this->permission_id = 0;
            if (! $this->isEdit) {
                $this->access = null;
            }
        }
    }

    /**
     * Al cambiar los bits seleccionados, busca o crea el permiso.
     */
    public function updatedSelectedBits(): void
    {
        $value = BitwiseHelper::combine($this->selectedBits);

        $permission = Permission::firstOrCreate(
            ['access' => $value],
            ['name'   => $value === 0 ? 'no access' : implode(' + ', $this->selectedBits)]
        );

        $this->permission_id = $permission->id;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'role_id'       => $this->role_id,
            'route_id'      => $this->route_id,
            'permission_id' => $this->permission_id,
        ];

        // updateOrCreate porque puede ya existir la combinación rol+ruta
        Access::updateOrCreate(
            ['role_id' => $this->role_id, 'route_id' => $this->route_id],
            ['permission_id' => $this->permission_id]
        );

        session()->flash('bwp_success', 'Acceso guardado correctamente.');
        $this->redirect(route('bwp.accesses.index'));
    }

    public function render()
    {
        // Filtrar roles según tipo seleccionado
        $rolesQuery = Role::orderBy('name');

        if ($this->roleType === 'base') {
            $rolesQuery->where('is_base_role', true);
        } elseif ($this->roleType === 'user') {
            $rolesQuery->where('is_base_role', false);
        }

        $roles  = $rolesQuery->get();
        $routes = AppRoute::orderBy('name')->get();
        $bits   = BitwiseHelper::all();

        return view('bwp::livewire.accesses.form', compact('roles', 'routes', 'bits'));
    }
}