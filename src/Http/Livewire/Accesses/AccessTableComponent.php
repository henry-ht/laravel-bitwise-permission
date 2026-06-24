<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Accesses;

use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use HenryHt\BitwisePermission\Models\Access;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class AccessTableComponent extends Component
{
    use WithPagination;

    public string $search      = '';
    public int    $roleFilter  = 0;
    public int    $routeFilter = 0;
    public string $roleType    = 'all'; // 'all' | 'base' | 'user'

    protected $queryString = [
        'search'     => ['except' => ''],
        'roleFilter' => ['except' => 0],
        'roleType'   => ['except' => 'all'],
    ];

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingRoleType(): void   { $this->resetPage(); }

    public function render()
    {
        $accesses = Access::query()
            ->with(['role', 'route', 'permission'])
            ->when($this->roleFilter,  fn($q) => $q->where('role_id',  $this->roleFilter))
            ->when($this->routeFilter, fn($q) => $q->where('route_id', $this->routeFilter))
            ->when($this->roleType === 'base', fn($q) =>
                $q->whereHas('role', fn($r) => $r->where('is_base_role', true))
            )
            ->when($this->roleType === 'user', fn($q) =>
                $q->whereHas('role', fn($r) => $r->where('is_base_role', false))
            )
            ->when($this->search, fn($q) =>
                $q->whereHas('route', fn($r) => $r->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('role',  fn($r) => $r->where('name', 'like', "%{$this->search}%")
                                                    ->orWhere('public_name', 'like', "%{$this->search}%"))
            )
            ->orderBy('role_id')
            ->paginate(20);

        $roles  = Role::orderBy('name')->get();
        $routes = AppRoute::orderBy('name')->get();
        $bits   = BitwiseHelper::all();

        return view('bwp::livewire.accesses.table', compact('accesses', 'roles', 'routes', 'bits'));
    }
}
