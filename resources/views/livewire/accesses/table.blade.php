<div class="bwp-wrap">

    @if(session('bwp_success'))
        <div class="bwp-alert bwp-alert--success">✓ {{ session('bwp_success') }}</div>
    @endif

    <div class="bwp-header">
        <h2 class="bwp-title">Accesos</h2>
        <a href="{{ route('bwp.accesses.create') }}" class="bwp-btn bwp-btn--primary">+ Nuevo acceso</a>
    </div>

    {{-- Toolbar: búsqueda + filtros --}}
    <div class="bwp-toolbar">

        <div class="bwp-search">
            <span class="bwp-search-icon">⌕</span>
            <input wire:model.live.debounce.300ms="search"
                   class="bwp-input"
                   placeholder="Buscar rol o ruta...">
        </div>

        <select wire:model.live="roleFilter" class="bwp-select bwp-filter">
            <option value="0">Todos los roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->public_name }}</option>
            @endforeach
        </select>

        <select wire:model.live="routeFilter" class="bwp-select bwp-filter">
            <option value="0">Todas las rutas</option>
            @foreach($routes as $route)
                <option value="{{ $route->id }}">{{ $route->name }}</option>
            @endforeach
        </select>

        {{-- Filtro tipo de rol --}}
        <div class="bwp-role-type-tabs">
            <button type="button"
                    wire:click="$set('roleType','all')"
                    class="bwp-role-type-tab {{ $roleType === 'all'  ? 'active' : '' }}">
                Todos
            </button>
            <button type="button"
                    wire:click="$set('roleType','base')"
                    class="bwp-role-type-tab {{ $roleType === 'base' ? 'active' : '' }}">
                Base
            </button>
            <button type="button"
                    wire:click="$set('roleType','user')"
                    class="bwp-role-type-tab {{ $roleType === 'user' ? 'active' : '' }}">
                Usuario
            </button>
        </div>

    </div>

    <div class="bwp-table-wrap">
        <table class="bwp-table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Ruta</th>
                    <th>Permiso</th>
                    <th>Bits activos</th>
                    <th class="bwp-col-actions"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($accesses as $access)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <span style="color:var(--bwp-text);font-weight:500;">
                                    {{ $access->role->public_name }}
                                </span>
                                @if($access->role->is_base_role)
                                    <span class="bwp-badge bwp-badge--base">base</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <code style="font-size:0.75rem;color:var(--bwp-accent);">
                                {{ $access->route->name }}
                            </code>
                        </td>
                        <td>
                            <span class="bwp-badge bwp-badge--muted">
                                {{ $access->permission->access }}
                            </span>
                        </td>
                        <td>
                            <div class="bwp-bits">
                                @if($access->permission->access === 0)
                                    <span class="bwp-bit bwp-bit--inactive">no access</span>
                                @else
                                    @foreach($bits as $bitName => $bitValue)
                                        @if(($access->permission->access & $bitValue) === $bitValue)
                                            <span class="bwp-bit bwp-bit--active">{{ $bitName }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="bwp-actions">
                                <a href="{{ route('bwp.accesses.edit', $access) }}"
                                   class="bwp-btn bwp-btn--ghost bwp-btn--sm">
                                    Editar
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="bwp-empty">Sin accesos configurados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="bwp-pagination">{{ $accesses->links() }}</div>
    </div>

</div>
