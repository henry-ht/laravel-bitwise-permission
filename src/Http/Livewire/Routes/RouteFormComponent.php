<?php

namespace HenryHt\BitwisePermission\Http\Livewire\Routes;

use HenryHt\BitwisePermission\Models\AppRoute;
use Livewire\Component;

class RouteFormComponent extends Component
{
    public ?AppRoute $route = null;
    public bool      $isEdit = false;

    public string $name        = '';
    public string $type        = 'web';
    public string $patch       = '';
    public string $base_url    = '';
    public string $description = '';

    protected function rules(): array
    {
        $table     = (new AppRoute)->getTable();
        $ignoreId  = $this->isEdit ? ",{$this->route->id}" : '';
        return [
            'name'       => "required|string|max:150|unique:{$table},name{$ignoreId}",
            'type'       => 'required|in:web,api',
            'patch'      => 'nullable|string|max:200',
            'base_url'   => 'nullable|string|max:200',
            'description'=> 'nullable|string|max:255',
        ];
    }

    public function mount(?int $routeId = null): void
    {
        if ($routeId) {
            $this->route       = AppRoute::findOrFail($routeId);
            $this->isEdit      = true;
            $this->name        = $this->route->name;
            $this->type        = $this->route->type;
            $this->patch       = $this->route->patch ?? '';
            $this->base_url    = $this->route->base_url ?? '';
            $this->description = $this->route->description ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'type'        => $this->type,
            'patch'       => $this->patch    ?: null,
            'base_url'    => $this->base_url ?: null,
            'description' => $this->description ?: null,
        ];

        $this->isEdit
            ? $this->route->update($data)
            : AppRoute::create($data);

        session()->flash('bwp_success', $this->isEdit ? 'Ruta actualizada.' : 'Ruta creada.');
        $this->redirect(route('bwp.routes.index'));
    }

    public function render()
    {
        return view('bwp::livewire.routes.form');
    }
}
