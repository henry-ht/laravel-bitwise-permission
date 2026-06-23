<div class="bwp-wrap">
    <div class="bwp-header">
        <h2 class="bwp-title">{{ $isEdit ? 'Editar rol' : 'Nuevo rol' }}</h2>
        <a href="{{ route('bwp.roles.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    <form wire:submit="save">
        <div class="bwp-card" style="margin-bottom:1.5rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="bwp-field">
                    <label class="bwp-label">Nombre (slug)</label>
                    <input wire:model="name" class="bwp-input" placeholder="admin">
                    @error('name')<p class="bwp-error">{{ $message }}</p>@enderror
                </div>
                <div class="bwp-field">
                    <label class="bwp-label">Nombre público</label>
                    <input wire:model="public_name" class="bwp-input" placeholder="Administrador">
                    @error('public_name')<p class="bwp-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="bwp-field">
                <label class="bwp-label">Descripción</label>
                <input wire:model="description" class="bwp-input" placeholder="Opcional">
            </div>
            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:var(--bwp-muted);cursor:pointer;">
                <input type="checkbox" wire:model="is_base_role" style="accent-color:var(--bwp-accent);">
                Rol base del sistema
            </label>
        </div>

        {{-- Permisos por ruta --}}
        <div class="bwp-card" style="margin-bottom:1.5rem;">
            <h3 style="font-size:0.9rem;font-weight:700;color:var(--bwp-text);margin-bottom:1rem;">Accesos por ruta</h3>
            <div class="bwp-table-wrap">
                <table class="bwp-table">
                    <thead><tr><th>Ruta</th><th>Tipo</th><th>Permiso</th></tr></thead>
                    <tbody>
                        @foreach($routes as $route)
                            <tr>
                                <td style="color:var(--bwp-text);font-weight:500;">{{ $route->name }}</td>
                                <td><span class="bwp-badge bwp-badge--muted">{{ $route->type }}</span></td>
                                <td>
                                    <select wire:model="routePermissions.{{ $route->id }}" class="bwp-select" style="width:auto;">
                                        <option value="">Sin acceso</option>
                                        @foreach($permissions as $permission)
                                            <option value="{{ $permission->id }}">
                                                {{ $permission->name }} ({{ $permission->access }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Menús --}}
        <div class="bwp-card" style="margin-bottom:1.5rem;">
            <h3 style="font-size:0.9rem;font-weight:700;color:var(--bwp-text);margin-bottom:1rem;">Visibilidad de menú</h3>
            @foreach($menus as $menu)
                <label class="bwp-bit-check" style="margin-bottom:0.5rem;">
                    <input type="checkbox" wire:model="menuAccess.{{ $menu->id }}">
                    {{ $menu->public_name }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="bwp-btn bwp-btn--primary">
            {{ $isEdit ? 'Guardar cambios' : 'Crear rol' }}
        </button>
    </form>
</div>
