<div class="bwp-wrap">

    <div class="bwp-header">
        <h2 class="bwp-title">Permisos de menú por rol</h2>
        <a href="{{ route('bwp.menus.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    @if($saved)
        <div class="bwp-alert bwp-alert--success">✓ {{ $savedMsg }}</div>
    @endif

    {{-- Selector de rol --}}
    <div class="bwp-card" style="margin-bottom:1.25rem;">
        <p class="bwp-card__title">Seleccionar rol</p>

        <div class="bwp-grid bwp-grid--2">
            <div class="bwp-field">
                <label class="bwp-label">Filtrar por tipo</label>
                <select wire:model.live="roleType" class="bwp-select">
                    <option value="all">Todos los roles</option>
                    <option value="base">Roles base</option>
                    <option value="user">Roles de usuario</option>
                </select>
            </div>

            <div class="bwp-field">
                <label class="bwp-label">Rol</label>
                <select wire:model.live="selectedRoleId" class="bwp-select">
                    <option value="0">Seleccionar rol...</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->public_name }}
                            @if($role->is_base_role) — base @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Lista de menús del rol seleccionado --}}
    @if($selectedRoleId && $selectedRole)

        <div class="bwp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
                <p class="bwp-card__title" style="margin:0;border:none;padding:0;">
                    Menús visibles para
                    <strong style="color:var(--bwp-accent);">{{ $selectedRole->public_name }}</strong>
                </p>
                <div style="display:flex;gap:0.5rem;">
                    <button type="button"
                            wire:click="enableAll"
                            class="bwp-btn bwp-btn--secondary bwp-btn--sm"
                            style="color:var(--bwp-success);">
                        ✓ Habilitar todos
                    </button>
                    <button type="button"
                            wire:click="disableAll"
                            class="bwp-btn bwp-btn--secondary bwp-btn--sm"
                            style="color:var(--bwp-danger);">
                        ✗ Deshabilitar todos
                    </button>
                </div>
            </div>

            @if($menus->isEmpty())
                <div class="bwp-empty">
                    No hay ítems de menú.
                    <a href="{{ route('bwp.menus.create') }}" style="color:var(--bwp-accent);">Crear uno</a>.
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:0.35rem;">
                    @foreach($menus as $menu)

                        {{-- Ítem raíz --}}
                        <div style="background:var(--bwp-surface-alt);border:1px solid var(--bwp-border);border-radius:var(--bwp-radius);overflow:hidden;">
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 0.85rem;">
                                <div style="display:flex;align-items:center;gap:0.6rem;">
                                    {{-- @if($menu->icon)
                                        <span style="color:var(--bwp-accent);font-size:0.85rem;width:16px;text-align:center;">
                                            {!! $menu->icon !!}
                                        </span>
                                    @endif --}}
                                    <span style="font-weight:600;color:var(--bwp-text);">
                                        {{ $menu->public_name }}
                                    </span>
                                    @if($menu->patch)
                                        <code style="font-size:0.7rem;color:var(--bwp-dim);">{{ $menu->patch }}</code>
                                    @endif
                                </div>

                                <label class="bwp-toggle">
                                    <input type="checkbox"
                                           wire:click="toggle({{ $menu->id }})"
                                           {{ ($state[$menu->id] ?? false) ? 'checked' : '' }}>
                                    <span class="bwp-toggle__slider"></span>
                                </label>
                            </div>

                            {{-- Hijos --}}
                            @if($menu->childrenOrdered->count())
                                <div style="border-top:1px solid var(--bwp-border);background:var(--bwp-bg);">
                                    @foreach($menu->childrenOrdered as $child)
                                        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.55rem 0.85rem 0.55rem 2rem;border-bottom:1px solid var(--bwp-border-subtle);">
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <span style="color:var(--bwp-dim);font-size:0.7rem;">└</span>
                                                {{-- @if($child->icon)
                                                    <span style="color:var(--bwp-muted);font-size:0.8rem;width:14px;text-align:center;">
                                                        {!! $child->icon !!}
                                                    </span>
                                                @endif --}}
                                                <span style="color:var(--bwp-muted);">{{ $child->public_name }}</span>
                                                @if($child->patch)
                                                    <code style="font-size:0.7rem;color:var(--bwp-dim);">{{ $child->patch }}</code>
                                                @endif
                                            </div>

                                            <label class="bwp-toggle">
                                                <input type="checkbox"
                                                       wire:click="toggle({{ $child->id }})"
                                                       {{ ($state[$child->id] ?? false) ? 'checked' : '' }}>
                                                <span class="bwp-toggle__slider"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    @endforeach
                </div>
            @endif
        </div>

    @elseif(! $selectedRoleId)
        <div class="bwp-empty">
            Selecciona un rol para gestionar su visibilidad de menú.
        </div>
    @endif

</div>