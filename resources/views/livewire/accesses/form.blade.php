<div class="bwp-wrap">
    <div class="bwp-header">
        <h2 class="bwp-title">{{ $isEdit ? 'Editar acceso' : 'Nuevo acceso' }}</h2>
        <a href="{{ route('bwp.accesses.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    <form wire:submit="save">
        <div class="bwp-card">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div class="bwp-field">
                    <label class="bwp-label">Rol</label>
                    <select wire:model="role_id" class="bwp-select">
                        <option value="0">Seleccionar rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->public_name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="bwp-error">{{ $message }}</p>@enderror
                </div>
                <div class="bwp-field">
                    <label class="bwp-label">Ruta</label>
                    <select wire:model="route_id" class="bwp-select">
                        <option value="0">Seleccionar ruta...</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                    </select>
                    @error('route_id')<p class="bwp-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bwp-field">
                <label class="bwp-label">Permisos (selecciona los bits)</label>
                <p style="font-size:0.75rem;color:var(--bwp-dim);margin-bottom:0.75rem;">
                    Sin <strong>view</strong> seleccionado, el usuario no puede entrar a la vista aunque tenga otros bits activos.
                </p>
                <div class="bwp-bit-grid">
                    @foreach($bits as $bitName => $bitValue)
                        <label class="bwp-bit-check">
                            <input type="checkbox" wire:model.live="selectedBits" value="{{ $bitName }}">
                            {{ $bitName }}
                            <span style="margin-left:auto;font-size:0.65rem;color:var(--bwp-dim);">{{ $bitValue }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permission_id')<p class="bwp-error">{{ $message }}</p>@enderror
            </div>

            @if(count($selectedBits) > 0)
                <div style="background:#252a3a;border:1px solid var(--bwp-border);border-radius:var(--bwp-radius);padding:0.75rem;margin-top:0.75rem;">
                    <p style="font-size:0.75rem;color:var(--bwp-muted);margin-bottom:0.35rem;">Valor resultante</p>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <span style="font-size:1.5rem;font-weight:800;color:var(--bwp-accent);">
                            {{ array_sum(array_map(fn($b) => config("bitwise-permission.bits.{$b}", 0), $selectedBits)) }}
                        </span>
                        <div class="bwp-bits">
                            @foreach($selectedBits as $bit)
                                <span class="bwp-bit bwp-bit--active">{{ $bit }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div style="margin-top:1.5rem;">
                <button type="submit" class="bwp-btn bwp-btn--primary">
                    {{ $isEdit ? 'Guardar cambios' : 'Crear acceso' }}
                </button>
            </div>
        </div>
    </form>
</div>
