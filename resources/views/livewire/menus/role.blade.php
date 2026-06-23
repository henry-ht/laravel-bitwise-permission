<div class="bwp-wrap">

    <div class="bwp-header">
        <h2 class="bwp-title">Permisos de menú por rol</h2>
        <a href="{{ route('bwp.menus.index') }}" class="bwp-btn bwp-btn--ghost">← Volver</a>
    </div>

    @if($saved)
        <div class="bwp-alert bwp-alert--success">✓ {{ $savedMsg }}</div>
    @endif

    {{-- Filtro de tipo de rol --}}
    <div class="bwp-role-type-tabs" style="max-width:320px;margin-bottom:1.25rem;">
        <button type="button"
                wire:click="$set('roleType','all')"
                class="bwp-role-type-tab {{ $roleType === 'all' ? 'active' : '' }}">
            Todos
        </button>
        <button type="button"
                wire:click="$set('roleType','base')"
                class="bwp-role-type-tab {{ $roleType === 'base' ? 'active' : '' }}">
            Roles base
        </button>
        <button type="button"
                wire:click="$set('roleType','user')"
                class="bwp-role-type-tab {{ $roleType === 'user' ? 'active' : '' }}">
            Roles de usuario
        </button>
    </div>

    @if($roles->isEmpty())
        <div class="bwp-empty">No hay roles para mostrar con este filtro.</div>
    @else
        <div class="bwp-table-wrap">
            <table class="bwp-menu-role-table">
                <thead>
                    <tr>
                        <th style="min-width:180px;">Ítem de menú</th>
                        @foreach($roles as $role)
                            <th style="text-align:center;min-width:130px;">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:0.3rem;">
                                    <span>{{ $role->public_name }}</span>
                                    @if($role->is_base_role)
                                        <span class="bwp-badge bwp-badge--base">base</span>
                                    @endif
                                    <div style="display:flex;gap:0.25rem;margin-top:0.2rem;">
                                        <button type="button"
                                                wire:click="enableAll({{ $role->id }})"
                                                class="bwp-btn bwp-btn--ghost bwp-btn--sm"
                                                title="Habilitar todos"
                                                style="font-size:0.65rem;padding:0.15rem 0.4rem;color:var(--bwp-success);">
                                            ✓ todos
                                        </button>
                                        <button type="button"
                                                wire:click="disableAll({{ $role->id }})"
                                                class="bwp-btn bwp-btn--ghost bwp-btn--sm"
                                                title="Deshabilitar todos"
                                                style="font-size:0.65rem;padding:0.15rem 0.4rem;color:var(--bwp-danger);">
                                            ✗ todos
                                        </button>
                                    </div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                        {{-- Ítem raíz --}}
                        <tr style="background:rgba(59,130,246,0.03);">
                            <td>
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    @if($menu->icon)
                                        <span style="color:var(--bwp-accent);width:14px;text-align:center;font-size:0.8rem;">
                                            {!! $menu->icon !!}
                                        </span>
                                    @endif
                                    <span style="font-weight:600;color:var(--bwp-text);">{{ $menu->public_name }}</span>
                                </div>
                            </td>
                            @foreach($roles as $role)
                                <td style="text-align:center;">
                                    <label class="bwp-toggle">
                                        <input type="checkbox"
                                               wire:click="toggle({{ $menu->id }}, {{ $role->id }})"
                                               {{ ($state["{$menu->id}.{$role->id}"] ?? false) ? 'checked' : '' }}>
                                        <span class="bwp-toggle__slider"></span>
                                    </label>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Ítems hijos --}}
                        @foreach($menu->childrenOrdered as $child)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:0.5rem;padding-left:1.25rem;">
                                        <span style="color:var(--bwp-dim);font-size:0.75rem;">└</span>
                                        @if($child->icon)
                                            <span style="color:var(--bwp-muted);width:14px;text-align:center;font-size:0.75rem;">
                                                {!! $child->icon !!}
                                            </span>
                                        @endif
                                        <span style="color:var(--bwp-muted);">{{ $child->public_name }}</span>
                                    </div>
                                </td>
                                @foreach($roles as $role)
                                    <td style="text-align:center;">
                                        <label class="bwp-toggle">
                                            <input type="checkbox"
                                                   wire:click="toggle({{ $child->id }}, {{ $role->id }})"
                                                   {{ ($state["{$child->id}.{$role->id}"] ?? false) ? 'checked' : '' }}>
                                            <span class="bwp-toggle__slider"></span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                    @empty
                        <tr>
                            <td colspan="{{ $roles->count() + 1 }}" class="bwp-empty">
                                No hay ítems de menú. <a href="{{ route('bwp.menus.create') }}" style="color:var(--bwp-accent);">Crear uno</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

</div>