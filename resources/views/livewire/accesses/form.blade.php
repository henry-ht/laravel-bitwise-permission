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
                            <option value="{{ $route->id }}">
                                {{ $route->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('route_id')
                        <p class="bwp-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Permiso actual --}}
            @if($isEdit && $role_id && $route_id)
                <div class="bwp-alert bwp-alert--info" style="margin-top:0.75rem;margin-bottom:0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                        <span>Permiso actual — modifica los bits y guarda.</span>
                        <span style="font-weight:700;color:var(--bwp-accent);font-size:1rem;">
                            {{ $currentAccess }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Bits --}}
        <div class="bwp-card">
            <p class="bwp-card__title">Permisos (bits)</p>
            <p class="bwp-hint" style="margin-bottom:1rem;">
                Sin <strong style="color:var(--bwp-text);">view</strong> seleccionado
                el usuario no puede entrar a la vista aunque tenga otros bits activos.
            </p>

            {{-- Grid — wire:model.live maneja checked, NO poner 'checked' manual --}}
            <div class="bwp-bit-grid">
                @foreach($bits as $bitName => $bitValue)
                    <label class="bwp-bit-check">
                        <input type="checkbox"
                               wire:model.live="selectedBits"
                               value="{{ $bitName }}">
                        {{ $bitName }}
                        <span class="bwp-bit-value">{{ $bitValue }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Resultado --}}
            <div class="bwp-bit-result">
                @php
                    $resultValue = array_sum(
                        array_map(
                            fn($b) => config("bitwise-permission.bits.{$b}", 0),
                            $selectedBits
                        )
                    );
                @endphp

                <div>
                    <p class="bwp-bit-result__label">Valor resultante</p>
                    <span class="bwp-bit-result__value"
                          style="{{ $resultValue === 0 ? 'color:var(--bwp-dim)' : '' }}">
                        {{ $resultValue }}
                    </span>
                </div>

                <div class="bwp-bits">
                    @if($resultValue === 0)
                        <span class="bwp-badge bwp-badge--muted">no access</span>
                    @else
                        @foreach($selectedBits as $bit)
                            <span class="bwp-bit bwp-bit--active">{{ $bit }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            @error('permission_id')
                <p class="bwp-error" style="margin-top:0.5rem;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bwp-btn bwp-btn--primary">
            {{ $isEdit ? 'Guardar cambios' : 'Crear acceso' }}
        </button>

    </form>
</div>