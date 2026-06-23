<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Accesses;

use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\Role;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use Livewire\Component;
use Livewire\WithPagination;

class AccessTableComponent extends Component
{
    use WithPagination;

    public string $search      = '';
    public int    $roleFilter  = 0;
    public int    $routeFilter = 0;

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }

    public function render()
    {
        $accesses = Access::query()
            ->with(['role', 'route', 'permission'])
            ->when($this->roleFilter,  fn($q) => $q->where('role_id',  $this->roleFilter))
            ->when($this->routeFilter, fn($q) => $q->where('route_id', $this->routeFilter))
            ->when($this->search, fn($q) =>
                $q->whereHas('route', fn($r) => $r->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('role', fn($r) => $r->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy('role_id')
            ->paginate(20);

        $roles      = Role::orderBy('name')->get();
        $routes     = AppRoute::orderBy('name')->get();
        $bits       = BitwiseHelper::all();

        return view('bwp::livewire.accesses.table', compact('accesses', 'roles', 'routes', 'bits'));
    }
}
