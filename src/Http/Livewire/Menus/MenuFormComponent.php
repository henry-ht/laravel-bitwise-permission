<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Menus;

use HenryHt\BitwisePermission\Models\Menu;
use Livewire\Component;

class MenuFormComponent extends Component
{
    public ?Menu $menu   = null;
    public bool  $isEdit = false;

    public string $name        = '';
    public string $public_name = '';
    public string $patch       = '';
    public string $icon        = '';
    public int    $order       = 0;
    public ?int   $father_id   = null;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'public_name' => 'required|string|max:150',
            'patch'       => 'nullable|string|max:200',
            'icon'        => 'nullable|string|max:100',
            'order'       => 'integer|min:0',
            'father_id'   => 'nullable|integer|exists:' . (new Menu)->getTable() . ',id',
        ];
    }

    public function mount(?int $menuId = null): void
    {
        if ($menuId) {
            $this->menu        = Menu::findOrFail($menuId);
            $this->isEdit      = true;
            $this->name        = $this->menu->name;
            $this->public_name = $this->menu->public_name;
            $this->patch       = $this->menu->patch ?? '';
            $this->icon        = $this->menu->icon ?? '';
            $this->order       = $this->menu->order;
            $this->father_id   = $this->menu->father_id;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'public_name' => $this->public_name,
            'patch'       => $this->patch     ?: null,
            'icon'        => $this->icon      ?: null,
            'order'       => $this->order,
            'father_id'   => $this->father_id ?: null,
        ];

        $this->isEdit
            ? $this->menu->update($data)
            : Menu::create($data);

        session()->flash('bwp_success', $this->isEdit ? 'Menú actualizado.' : 'Menú creado.');
        // $this->redirect(route('bwp.menus.index'));
    }

    public function render()
    {
        $parents = Menu::root()->orderBy('order')->get();
        return view('bwp::livewire.menus.form', compact('parents'));
    }
}
