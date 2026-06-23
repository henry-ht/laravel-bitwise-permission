<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Routes;

use HenryHt\BitwisePermission\Models\AppRoute;
use Livewire\Component;
use Livewire\WithPagination;

class RouteTableComponent extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $typeFilter = '';
    public ?int   $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }
    public function cancelDelete(): void          { $this->deleteId = null; }

    public function delete(): void
    {
        if ($this->deleteId) {
            AppRoute::findOrFail($this->deleteId)->delete();
            $this->deleteId = null;
            session()->flash('bwp_success', 'Ruta eliminada.');
        }
    }

    public function render()
    {
        $routes = AppRoute::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('patch', 'like', "%{$this->search}%")
            )
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->orderBy('name')
            ->paginate(15);

        return view('bwp::livewire.routes.table', compact('routes'));
    }
}
