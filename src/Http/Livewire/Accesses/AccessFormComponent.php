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
    public ?int $accessId     = null;
    public bool $isEdit       = false;

    public int   $role_id       = 0;
    public int   $route_id      = 0;
    public int   $permission_id = 0;
    public array $selectedBits  = [];

    // Valor del permiso actual ANTES de editar — solo informativo
    public int $currentAccess = 0;

    protected function rules(): array
    {
        return [
            'role_id'       => 'required|integer|min:1',
            'route_id'      => 'required|integer|min:1',
            'permission_id' => 'required|integer|min:1',
        ];
    }

    public function mount(): void
    {
        if (! $this->accessId) {
            return;
        }

        $access = Access::with('permission')->find($this->accessId);

        if (! $access) {
            return;
        }

        $this->isEdit        = true;
        $this->role_id       = $access->role_id;
        $this->route_id      = $access->route_id;
        $this->permission_id = $access->permission_id;
        $this->currentAccess = $access->permission->access;
        $this->selectedBits  = BitwiseHelper::decode($access->permission->access);
    }

    public function updatedRoleId(): void
    {
        $this->loadExistingAccess();
    }

    public function updatedRouteId(): void
    {
        $this->loadExistingAccess();
    }

    protected function loadExistingAccess(): void
    {
        if (! $this->role_id || ! $this->route_id) {
            return;
        }

        $existing = Access::where('role_id',  $this->role_id)
                          ->where('route_id', $this->route_id)
                          ->with('permission')
                          ->first();

        if ($existing) {
            $this->accessId      = $existing->id;
            $this->isEdit        = true;
            $this->permission_id = $existing->permission_id;
            $this->currentAccess = $existing->permission->access;
            $this->selectedBits  = BitwiseHelper::decode($existing->permission->access);
        } else {
            $this->accessId      = null;
            $this->isEdit        = false;
            $this->permission_id = 0;
            $this->currentAccess = 0;
            $this->selectedBits  = [];
        }
    }

    public function updatedSelectedBits(): void
    {
        $value = BitwiseHelper::combine($this->selectedBits);

        $permission = Permission::firstOrCreate(
            ['access' => $value],
            ['name'   => $value === 0
                ? 'no access'
                : implode(' + ', $this->selectedBits)
            ]
        );

        $this->permission_id = $permission->id;
    }

    public function save(): void
    {
        $this->validate();

        Access::updateOrCreate(
            ['role_id'  => $this->role_id, 'route_id' => $this->route_id],
            ['permission_id' => $this->permission_id]
        );

        session()->flash('bwp_success', 'Acceso guardado correctamente.');
        $this->redirect(route('bwp.accesses.index'));
    }

    public function render()
    {
        $roles  = Role::orderBy('is_base_role', 'desc')->orderBy('name')->get();
        $routes = AppRoute::orderBy('name')->get();
        $bits   = BitwiseHelper::all();

        return view('bwp::livewire.accesses.form', compact('roles', 'routes', 'bits'));
    }
}