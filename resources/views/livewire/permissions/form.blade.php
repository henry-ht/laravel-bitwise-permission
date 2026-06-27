<div class="bwp-wrap">

    <div class="bwp-header">
        <h2 class="bwp-title">{{ $isEdit ? 'Editar permiso' : 'Nuevo permiso' }}</h2>
        <a href="{{ route('bwp.permissions.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    <form wire:submit="save">

        <div class="bwp-card">
            <p class="bwp-card__title">Información</p>

            <div class="bwp-field">
                <label class="bwp-label">Nombre</label>
                <input wire:model="name"
                       class="bwp-input"
                       placeholder="ej: read access, modify access with delete...">
                @error('name')
                    <p class="bwp-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bwp-card">
            <p class="bwp-card__title">Bits del permiso</p>
            <p class="bwp-hint" style="margin-bottom:1rem;">
                Sin <strong style="color:var(--bwp-text);">view</strong> seleccionado
                el usuario no puede entrar a la vista aunque tenga otros bits activos.
            </p>

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
            <div class="bwp-bit-result" style="margin-top:1rem;">
                <div>
                    <p class="bwp-bit-result__label">Valor resultante</p>
                    <span class="bwp-bit-result__value"
                          style="{{ $computedAccess === 0 ? 'color:var(--bwp-dim)' : '' }}">
                        {{ $computedAccess }}
                    </span>
                </div>
                <div class="bwp-bits">
                    @if($computedAccess === 0)
                        <span class="bwp-badge bwp-badge--muted">no access</span>
                    @else
                        @foreach($selectedBits as $bit)
                            <span class="bwp-bit bwp-bit--active">{{ $bit }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            @error('computedAccess')
                <p class="bwp-error" style="margin-top:0.5rem;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bwp-btn bwp-btn--primary">
            {{ $isEdit ? 'Guardar cambios' : 'Crear permiso' }}
        </button>

    </form>
</div>