<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Roles;

use HenryHt\BitwisePermission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RoleTableComponent extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $sortBy   = 'name';
    public string $sortDir  = 'asc';
    public ?int   $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'asc';
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            Role::findOrFail($this->deleteId)->delete();
            $this->deleteId = null;
            session()->flash('bwp_success', 'Rol eliminado correctamente.');
        }
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function render()
    {
        $roles = Role::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('public_name', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(15);

        return view('bwp::livewire.roles.table', compact('roles'));
    }
}
