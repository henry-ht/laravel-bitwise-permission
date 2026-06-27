<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Permissions;

use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use HenryHt\BitwisePermission\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionTableComponent extends Component
{
    use WithPagination;

    public string $search   = '';
    public ?int   $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }
    public function cancelDelete(): void          { $this->deleteId = null; }

    public function delete(): void
    {
        if ($this->deleteId) {
            Permission::findOrFail($this->deleteId)->delete();
            $this->deleteId = null;
            session()->flash('bwp_success', 'Permiso eliminado.');
        }
    }

    public function render()
    {
        $permissions = Permission::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy('access')
            ->paginate(20);

        // Excluir no_access (0) — no es un bit evaluable con &
        $bits = collect(BitwiseHelper::all())
            ->filter(fn($v) => $v > 0)
            ->all();

        return view('bwp::livewire.permissions.table', compact('permissions', 'bits'));
    }
}