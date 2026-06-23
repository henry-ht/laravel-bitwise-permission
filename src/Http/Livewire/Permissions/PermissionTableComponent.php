<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Permissions;

use HenryHt\BitwisePermission\Models\Permission;
use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionTableComponent extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $permissions = Permission::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy('access')
            ->paginate(20);

        $bits = BitwiseHelper::all();

        return view('bwp::livewire.permissions.table', compact('permissions', 'bits'));
    }
}
