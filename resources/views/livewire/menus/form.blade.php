<div class="bwp-wrap">
    <div class="bwp-header">
        <h2 class="bwp-title">{{ $isEdit ? 'Editar ítem' : 'Nuevo ítem de menú' }}</h2>
        <a href="{{ route('bwp.menus.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    <form wire:submit="save">
        <div class="bwp-card">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="bwp-field">
                    <label class="bwp-label">Nombre (slug)</label>
                    <input wire:model="name" class="bwp-input" placeholder="leads">
                    @error('name')<p class="bwp-error">{{ $message }}</p>@enderror
                </div>
                <div class="bwp-field">
                    <label class="bwp-label">Nombre público</label>
                    <input wire:model="public_name" class="bwp-input" placeholder="Leads">
                    @error('public_name')<p class="bwp-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="bwp-field">
                    <label class="bwp-label">Ruta Laravel</label>
                    <input wire:model="patch" class="bwp-input" placeholder="leads.index">
                </div>
                <div class="bwp-field">
                    <label class="bwp-label">Icono</label>
                    <input wire:model="icon" class="bwp-input" placeholder="fa-solid fa-users">
                </div>
                <div class="bwp-field">
                    <label class="bwp-label">Orden</label>
                    <input wire:model="order" type="number" class="bwp-input" min="0">
                </div>
            </div>

            <div class="bwp-field">
                <label class="bwp-label">Ítem padre (opcional)</label>
                <select wire:model="father_id" class="bwp-select">
                    <option value="">Sin padre (raíz)</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->public_name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bwp-btn bwp-btn--primary">
                {{ $isEdit ? 'Guardar cambios' : 'Crear ítem' }}
            </button>
        </div>
    </form>
</div>
