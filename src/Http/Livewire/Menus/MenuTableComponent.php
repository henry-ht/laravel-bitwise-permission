<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Menus;

use HenryHt\BitwisePermission\Models\Menu;
use Livewire\Component;

class MenuTableComponent extends Component
{
    public ?int $deleteId = null;

    public function confirmDelete(int $id): void { $this->deleteId = $id; }
    public function cancelDelete(): void          { $this->deleteId = null; }

    public function delete(): void
    {
        if ($this->deleteId) {
            Menu::findOrFail($this->deleteId)->delete();
            $this->deleteId = null;
            session()->flash('bwp_success', 'Ítem de menú eliminado.');
        }
    }

    public function render()
    {
        $menus = Menu::root()
            ->orderBy('order')
            ->with('childrenOrdered')
            ->get();

        return view('bwp::livewire.menus.table', compact('menus'));
    }
}
