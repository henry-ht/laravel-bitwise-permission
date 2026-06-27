<div class="bwp-wrap">

    <div class="bwp-header">
        <h2 class="bwp-title">{{ $isEdit ? 'Editar acceso' : 'Nuevo acceso' }}</h2>
        <a href="{{ route('bwp.accesses.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    <form wire:submit="save">

        {{-- Rol + Ruta --}}
        <div class="bwp-card">
            <p class="bwp-card__title">Rol y ruta</p>

            <div class="bwp-grid bwp-grid--2">
                <div class="bwp-field">
                    <label class="bwp-label">Rol</label>
                    <select wire:model.live="role_id" class="bwp-select">
                        <option value="0">Seleccionar rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">
                                {{ $role->public_name }}
                                @if($role->is_base_role) — base @endif
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="bwp-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bwp-field">
                    <label class="bwp-label">Ruta</label>
                    <select wire:model.live="route_id" class="bwp-select">
                        <option value="0">Seleccionar ruta...</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                    </select>
                    @error('route_id')
                        <p class="bwp-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($isEdit && $role_id && $route_id)
                <div class="bwp-alert bwp-alert--info" style="margin-top:0.75rem;margin-bottom:0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                        <span>Esta combinación ya tiene un permiso asignado.</span>
                        <span style="font-weight:700;color:var(--bwp-accent);">
                            valor actual: {{ $currentAccess }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Permiso --}}
        <div class="bwp-card">
            <p class="bwp-card__title">Permiso</p>

            <div class="bwp-field">
                <label class="bwp-label">Seleccionar permiso</label>
                <select wire:model.live="permission_id" class="bwp-select">
                    <option value="0">Seleccionar permiso...</option>
                    @foreach($permissions as $permission)
                        <option value="{{ $permission->id }}">
                            {{ $permission->name }}
                            ({{ $permission->access }})
                        </option>
                    @endforeach
                </select>
                @error('permission_id')
                    <p class="bwp-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bits activos del permiso seleccionado — solo informativo --}}
            @if($permission_id && $currentAccess > 0)
                <div class="bwp-bit-result" style="margin-top:0.75rem;">
                    <div>
                        <p class="bwp-bit-result__label">Valor</p>
                        <span class="bwp-bit-result__value">{{ $currentAccess }}</span>
                    </div>
                    <div class="bwp-bits">
                        @foreach($bits as $bitName => $bitValue)
                            @if(($currentAccess & $bitValue) === $bitValue)
                                <span class="bwp-bit bwp-bit--active">{{ $bitName }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @elseif($permission_id)
                <div class="bwp-bit-result" style="margin-top:0.75rem;">
                    <div>
                        <p class="bwp-bit-result__label">Valor</p>
                        <span class="bwp-bit-result__value" style="color:var(--bwp-dim);">0</span>
                    </div>
                    <span class="bwp-badge bwp-badge--muted">no access</span>
                </div>
            @endif
        </div>

        <button type="submit" class="bwp-btn bwp-btn--primary">
            {{ $isEdit ? 'Guardar cambios' : 'Crear acceso' }}
        </button>

    </form>
</div>