<div class="bwp-wrap">
    <div class="bwp-header">
        <h2 class="bwp-title">{{ $isEdit ? 'Editar ruta' : 'Nueva ruta' }}</h2>
        <a href="{{ route('bwp.routes.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    <form wire:submit="save">
        <div class="bwp-card">
            <div class="bwp-field">
                <label class="bwp-label">Nombre wildcard</label>
                <input wire:model="name" class="bwp-input" placeholder="leads.*">
                <p style="font-size:0.72rem;color:var(--bwp-dim);margin-top:0.25rem;">Usa * para agrupar todas las acciones de un recurso.</p>
                @error('name')<p class="bwp-error">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="bwp-field">
                    <label class="bwp-label">Tipo</label>
                    <select wire:model="type" class="bwp-select">
                        <option value="web">web</option>
                        <option value="api">api</option>
                    </select>
                </div>
                <div class="bwp-field">
                    <label class="bwp-label">Path</label>
                    <input wire:model="patch" class="bwp-input" placeholder="/leads">
                </div>
            </div>

            <div class="bwp-field">
                <label class="bwp-label">Descripción</label>
                <input wire:model="description" class="bwp-input" placeholder="Gestión de leads">
            </div>

            <button type="submit" class="bwp-btn bwp-btn--primary">
                {{ $isEdit ? 'Guardar cambios' : 'Crear ruta' }}
            </button>
        </div>
    </form>
</div>
