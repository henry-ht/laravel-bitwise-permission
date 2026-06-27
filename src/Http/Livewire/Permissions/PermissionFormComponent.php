<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Permissions;

use HenryHt\BitwisePermission\Helpers\BitwiseHelper;
use HenryHt\BitwisePermission\Models\Permission;
use Livewire\Component;

class PermissionFormComponent extends Component
{
    public ?int  $permissionId = null;
    public bool  $isEdit       = false;

    public string $name         = '';
    public array  $selectedBits = [];

    // Valor calculado en tiempo real
    public int $computedAccess = 0;

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|max:100',
            'selectedBits'  => 'array',
            'computedAccess'=> 'required|integer|min:0',
        ];
    }

    public function mount(): void
    {
        if (! $this->permissionId) {
            return;
        }

        $permission = Permission::find($this->permissionId);

        if (! $permission) {
            return;
        }

        $this->isEdit         = true;
        $this->name           = $permission->name;
        $this->computedAccess = $permission->access;
        $this->selectedBits   = BitwiseHelper::decode($permission->access);
    }

    /**
     * Al cambiar los bits — recalcula el valor resultante.
     */
    public function updatedSelectedBits(): void
    {
        $this->computedAccess = BitwiseHelper::combine($this->selectedBits);
    }

    public function save(): void
    {
        $this->validate();

        // Verificar que no exista otro permiso con el mismo access
        $existing = Permission::where('access', $this->computedAccess)
            ->when($this->isEdit, fn($q) => $q->where('id', '!=', $this->permissionId))
            ->first();

        if ($existing) {
            $this->addError('computedAccess',
                "Ya existe el permiso \"{$existing->name}\" con este mismo valor ({$this->computedAccess})."
            );
            return;
        }

        if ($this->isEdit) {
            Permission::findOrFail($this->permissionId)->update([
                'name'   => $this->name,
                'access' => $this->computedAccess,
            ]);
        } else {
            Permission::create([
                'name'   => $this->name,
                'access' => $this->computedAccess,
            ]);
        }

        session()->flash('bwp_success', $this->isEdit ? 'Permiso actualizado.' : 'Permiso creado.');
        $this->redirect(route('bwp.permissions.index'));
    }

    public function render()
    {
        // Bits sin no_access
        $bits = collect(BitwiseHelper::all())
            ->filter(fn($v) => $v > 0)
            ->all();

        return view('bwp::livewire.permissions.form', compact('bits'));
    }
}